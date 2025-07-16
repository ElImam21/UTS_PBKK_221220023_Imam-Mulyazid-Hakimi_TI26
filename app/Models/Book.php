<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'book_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'book_id',
        'title',
        'isbn',
        'publisher',
        'year_publised',
        'stock',
    ];
    public static function rules()
    {
        return [
            'title' => 'required|string|unique:books',
            'isbn' => 'required|string|unique:books',
            'publisher' => 'required|string',
            'year_publised' => 'required|string',
            'stock' => 'required|integer'
        ];
    }
}