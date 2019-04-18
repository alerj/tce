<?php

use App\Services\Reports\TCE;
use App\Services\Importer\Pessoal;
use App\Services\Importer\Pagamento;

Artisan::command('tce:import:pagamento {file}', function ($file) {
    app(Pagamento::class)->import($file);
})->describe('Display an inspiring quote');

Artisan::command('tce:import:pessoal {file}', function ($file) {
    app(Pessoal::class)->import($file);
})->describe('Display an inspiring quote');

Artisan::command('tce:report {file}', function ($file) {
    app(TCE::class)->toCsv($file);
})->describe('Display an inspiring quote');

//                                                                                                      1                                                                                                   2                                                                                                   3
//             0        1         2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0         1         2         3         4         5         6         7         8         9         0         1
//             1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890
//                   424.632-8 AARAO DE SOUZA MAIA                  440272  7140842710                           ASSESSOR PARLAMENTAR VII                              843,05      2.107,61                                                                                          158,34           0,00         1.601,06
//  $a5232 => "      421.798-0 YASMYM MALHADO REZENDE               211250 13937465782                           ASSISTENTE IX                                         281,02        702,55                                                                                           78,68           0,00           904,89\r\n";
//  $b5233 => "      201.843-0 YGOR LEONARDO DE O ANDRADE           211230 15945611840 ESPEC LEG NIV - 4 IND.170                               |     8.263,82                          0,00                         4.131,91        1.193,36                                       1.735,40       2.208,42         8.983,52\r\n";
//  $c5234 => "      422.752-6 YOHANNA BONAN DE CARVALHO PINTO      110000 15401236778                           ASSESSOR PARLAMENTAR VIII                             562,04      1.405,10                                         1.193,36                                         347,65          68,16         2.744,69\r\n";
//  $d5235 => "      424.834-0 YOLANDA CRISTINA BRAGA P DIAS        440276  2633770754                           ASSESSOR PARLAMENTAR VI                             1.124,07      2.810,16                                         4.773,44                                         642,33       1.348,60         6.716,74\r\n";
//  $a5236 => "      308.122-1 YURI DIMITRI DOS SANTOS P SCAL       440249 11668961709                           ASSESSOR PARLAMENTAR I                              2.810,18      7.025,45                                                                                                       1.835,43         8.000,20\r\n";
//                   200.880-3 HIPOLITO COSTA OLIVEIRA FILHO        440016 50882899791 ESPEC LEG NIV - 5 IND.250                                    11.369,69        2.810,18      7.034,93         9.835,63        6.821,81        6.310,51                         2.400,54      3.923,79       9.190,92        23.078,13
//                   201.358-9 HELIO SERGIO DE MORAES CRUZ          211000 35137673749 ESPEC LEG NIV - 5 IND.250 AUXILIAR I                         11.369,69        1.567,65      3.947,55        10.578,33        6.821,81        4.027,77        11.418,86                      4.027,77      10.459,91        33.747,64
