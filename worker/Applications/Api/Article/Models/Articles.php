<?php
/**
 * Created by PhpStorm.
 * User: liuweigang
 * Date: 15/01/2018
 * Time: 22:36
 */
namespace Models;

use  Illuminate\Database\Eloquent\Model  as Eloquent;


class Articles extends Eloquent{
    protected $table = 'articles';
    public $timestamps = false;
}