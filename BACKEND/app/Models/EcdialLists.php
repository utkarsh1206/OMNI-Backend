<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcdialLists extends Model
{
    use HasFactory;

    protected $table = 'ecdial_lists';

    protected $primaryKey = 'list_id';

    public $timestamps = true;

    protected $fillable = ['name', 'description', 'status', 'campaign_id'];

    public function ecdialListFields()
    {
        return $this->hasMany(EcdialListField::class, 'list_id', 'list_id');
    }
}
