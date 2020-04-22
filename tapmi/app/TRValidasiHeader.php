<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TRValidasiHeader extends Model
{
    //
    
    protected $connection = 'mobile_ins';
    protected $table = 'TR_VALIDASI_HEADER';
    protected $primaryKey = 'id_validasi';
    public $incrementing = false;
    public $timestamps = false;
    protected $fillable = [
        'id_validasi',
        'jumlah_ebcc_validated',
        'last_update'
    ]; 
}
