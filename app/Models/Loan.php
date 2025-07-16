<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Loan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'loans_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'loans_id',
        'user_id',
        'book_id',
        'return_date',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relasi ke Book
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'book_id');
    }
}