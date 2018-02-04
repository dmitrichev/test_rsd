<?php

namespace App\BruteForce;

/**
 * Class Password
 * Отвечает за перебор паролей для передачу в форму
 *
 * @package App\BruteForce
 */
class Password
{
    /**
     * Текущий пароль
     *
     * @var int
     */
    protected $password;

    /**
     * Массив паролей, которые не прошли проверку
     *
     * @var int[]
     */
    protected $failed_passwords = [];

    /**
     * Password constructor.
     *
     * @param int $start_password
     */
    public function __construct($start_password)
    {
        $this->password = $start_password;
    }

    /**
     * Возвращает новый пароль для перебора или один из тех, которые не прошли проверку
     *
     * @return int
     */
    public function getNextPassword()
    {
        if (empty($this->failed_passwords))
        {
            return $this->password++;
        }
        else
        {
            return array_shift($this->failed_passwords);
        }
    }

    /**
     * Добавляет новый пароль, который не прошел проверку для его повторного использования
     *
     * @param int $password
     */
    public function addFailedPassword($password)
    {
        $this->failed_passwords[] = $password;
    }
}