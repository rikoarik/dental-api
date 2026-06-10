<?php

namespace App\Enums;

enum ContentCategory: string
{
    case KesehatanGigi = 'kesehatan_gigi';
    case Teknologi = 'teknologi';
    case Klinik = 'klinik';
    case Edukasi = 'edukasi';
    case Umum = 'umum';
}
