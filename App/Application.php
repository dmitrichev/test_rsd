<?php

namespace App;
use App\BruteForce\Password;
use App\BruteForce\MultiRequest;

/**
 * Class Application
 * @package App
 */
class Application
{
    /**
     * @var array
     */
    public $config = [];

    /**
     * @var Application|null
     */
    protected static $instance = null;

    /**
     * @var float
     */
    protected $start;

    /**
     * Application constructor.
     */
    protected function __construct()
    {
        $this->config = include PROJECT_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'app.php';
    }

    /**
     * @return Application
     */
    public static function instance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Запускает работу приложения
     */
    public function run()
    {
        $this->start = microtime(true);

        $request = new MultiRequest($this->config['form_address'], $this->config['requests_per7_iteration']);

        $password_generator = new Password($this->config['start_password']);
        $request->setPasswordGenerator($password_generator);

        $result = $request->send();

        $this->renderResult($result);
    }

    /**
     * Выводит результат на страницу
     *
     * @param array $result
     */
    protected function renderResult($result)
    {
        if (!empty($result))
        {
            echo 'Код для формы: ' . $result['password']
                . '<br>'
                . 'Ссылка: <a href="' . $result['link'] . '">' . $result['link'] . '</a>';
        }

        echo '<br>';

        $execution_time = microtime(true) - $this->start;

        if ($execution_time > 60)
        {
            printf('Время выполнения: %02d:%02d', $execution_time / 60, $execution_time % 60);
        }
        else
        {
            printf('Время выполнения: %.1F сукунд', $execution_time);
        }
    }
}
