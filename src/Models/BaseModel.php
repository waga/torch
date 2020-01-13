<?php

namespace Torch\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    /**
     * Login
     * 
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login(string $email, string $password) : array
    {
        return $this
            ->setTable('users')
            ->db
            ->query('
                SELECT * 
                FROM `users` 
                WHERE 
                    `email` = ? AND 
                    `password` = ? 
                LIMIT 1
            ', [
                $email, 
                $password
            ])
            ->getRowArray();
    }
    
    /**
     * Register
     * 
     * @param array $user
     * @return integer
     */
    public function register(array $user)
    {
        $this
            ->setTable('users')
            ->db
            ->query('
                INSERT INTO `users` 
                SET 
                    `email` = ?,
                    `password` = ?
            ', [
                $user['email'],
                $user['password'],
            ]);
        return $this->db->insertID();
    }
}
