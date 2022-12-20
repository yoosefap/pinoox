<?php
/**
 *
 *
 */

namespace pinoox\model;

use pinoox\storage\Model;


class MigrationModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'migration';

    public $timestamps = false;

    /**
     * @param string[] $fillable
     */
    protected $fillable = ['migration', 'batch', 'app'];

}
