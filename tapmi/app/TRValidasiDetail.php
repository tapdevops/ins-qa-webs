<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TRValidasiDetail extends Model
{
    //  
    protected $connection = 'mobile_ins';
    protected $table = 'TR_VALIDASI_DETAIL';
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = 'UUID';
    protected $fillable = [
        'uuid',
        'id_validasi',
        'tanggal_ebcc',
        'nik_krani_buah',
        'nama_krani_buah',
        'ba_code',
        'ba_name',
        'afd_code',
        'block_code',
        'block_name',
        'no_tph',
        'no_bcc',
        'jjg_ebcc_bm',
        'jjg_ebcc_bk',
        'jjg_ebcc_ms',
        'jjg_ebcc_or',
        'jjg_ebcc_bb',
        'jjg_ebcc_jk',
        'jjg_ebcc_ba',
        'jjg_ebcc_total',
        'jjg_ebcc_1',
        'jjg_ebcc_2',
        'jjg_validate_bm',
        'jjg_validate_bk',
        'jjg_validate_ms',
        'jjg_validate_or',
        'jjg_validate_bb',
        'jjg_validate_jk',
        'jjg_validate_ba',
        'jjg_validate_total',
        'jjg_validate_1',
        'jjg_validate_2',
        'kondisi_foto',
        'insert_time',
        'insert_user',
        'insert_user_fullname',
        'insert_user_userrole'
    ];
}
