<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListTemplateMap extends Model
{
    use HasFactory;

    protected $fillable = ['list_id', 'template_id'];

    public function list()
    {
        return $this->belongsTo(EcdialLists::class, 'list_id', 'list_id');
    }

    public function template()
    {
        return $this->belongsTo(MailTemplate::class, 'template_id', 'id');
    }
}
