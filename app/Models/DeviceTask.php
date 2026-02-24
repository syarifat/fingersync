<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceTask extends Model
{
    use HasFactory;
    protected $table = 'device_tasks';
    protected $guarded = ['id'];
}