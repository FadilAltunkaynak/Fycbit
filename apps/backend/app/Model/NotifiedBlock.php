<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifiedBlock extends Model
{
    use HasFactory;
    protected $fillable = [
        "network_id",
        "block_number",
        "token_block_number",
        "node_block",
        "error",
    ];
}
