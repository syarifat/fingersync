<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FingerprintInbox extends Model
{
    use HasFactory;
    protected $table = 'fingerprint_inboxes';
    protected $guarded = ['id'];
}