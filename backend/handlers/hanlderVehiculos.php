<?php
class HanlderVehiculos
{
    private $pdo;
    public function __construct()
    {
        $this->pdo = DB::connect();
    }
    
}