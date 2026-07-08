<?php

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

ob_start();

require 'libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require 'coneccion.php';


// ===============================
// ID DEL REGISTRO ELIMINATORIA
// ===============================

$id = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);


if(!$id){

    die("ID eliminatoria inválido");

}



// ===============================
// CONSULTAR PRONOSTICO ELIMINATORIA
// ===============================

$stmt=$conn->prepare("

SELECT 
qe.*,
q.nombre,
q.correo,
q.empresa

FROM quinielas_eliminatorias qe

INNER JOIN quinielas q
ON q.id = qe.quiniela_id

WHERE qe.id = ?

");


$stmt->bind_param("i",$id);

$stmt->execute();


$resultado=$stmt->get_result();


$registro=$resultado->fetch_assoc();



if(!$registro){

    die("No existe el registro");

}



// ===============================
// DATOS
// ===============================

$participante=$registro['nombre'];

$folio=$registro['quiniela_id'];

$fase=$registro['fase'];


$pronosticos=json_decode(
    $registro['quiniela_json'],
    true
);




// ===============================
// OBTENER PARTIDOS
// ===============================


$partidos=[];


foreach($pronosticos as $p){


    $id_partido=intval($p['partido_id']);


    $sql=$conn->prepare("

    SELECT *
    FROM partidos_eliminatorias
    WHERE id=?

    ");


    $sql->bind_param(
        "i",
        $id_partido
    );


    $sql->execute();


    $r=$sql->get_result();


    $partido=$r->fetch_assoc();



    if($partido){


        $partidos[]=[

            "datos"=>$partido,

            "pronostico"=>$p

        ];


    }

}





// ===============================
// TITULO FASE
// ===============================


switch($fase){


case 32:
$titulo="Dieciseisavos de Final";
break;


case 16:
$titulo="Octavos de Final";
break;


case 8:
$titulo="Cuartos de Final";
break;


case 4:
$titulo="Semifinal";
break;


case 3:
$titulo="Tercer Lugar";
break;


case 2:
$titulo="Final";
break;


default:
$titulo="Eliminatorias";

}




// ===============================
// HTML PDF
// ===============================


$html='

<html>

<head>

<style>


body{

font-family: Arial;

color:#333;

}


.header{

background:#003c69;

color:white;

padding:20px;

text-align:center;

}


.info{

margin:20px 0;

padding:15px;

border:1px solid #ddd;

}


.titulo{

background:#f9a825;

padding:10px;

font-weight:bold;

margin-top:20px;

}



table{

width:100%;

border-collapse:collapse;

margin-top:15px;

}


th{

background:#003c69;

color:white;

padding:10px;

}


td{

border:1px solid #ddd;

padding:10px;

text-align:center;

}



.badge{

padding:5px 10px;

border-radius:5px;

font-weight:bold;

}


.badge1{

background:#d9fdd3;

}


.badgex{

background:#fff3cd;

}


.badge2{

background:#dbeafe;

}


</style>


</head>


<body>


<div class="header">

<h1>
Quiniela Mundial 2026
</h1>

<h2>
'.$titulo.'
</h2>


</div>



<div class="info">

<strong>Participante:</strong>
'.$participante.'

<br>

<strong>Folio:</strong>
'.$folio.'

<br>

<strong>Fecha:</strong>
'.$registro['fecha_envio'].'

</div>



<div class="titulo">

Pronósticos registrados

</div>


<table>

<thead>

<tr>

<th>Partido</th>

<th>Encuentro</th>

<th>Marcador</th>

<th>Pronóstico</th>

</tr>

</thead>


<tbody>';




// ===============================
// TABLA PARTIDOS
// ===============================


foreach($partidos as $item){


$p=$item['datos'];

$r=$item['pronostico'];



$voto=$r['res'];


$texto=

$voto=="1"
?
"Local (1)"
:
(
$voto=="x"
?
"Empate (X)"
:
"Visitante (2)"
);



$html.='

<tr>


<td>

'.$p['partido_idx'].'

</td>


<td>

'.$p['local'].'
<br>
VS
<br>
'.$p['visita'].'

</td>


<td>

'.$r['gl'].' - '.$r['gv'].'

</td>



<td>

<span class="badge badge'.$voto.'">

'.$texto.'

</span>


</td>


</tr>


';



}



$html.='

</tbody>

</table>


</body>

</html>';




// ===============================
// GENERAR PDF
// ===============================


$options=new Options();

$options->set(
'isHtml5ParserEnabled',
true
);


$dompdf=new Dompdf($options);


$dompdf->loadHtml($html);


$dompdf->setPaper(
'A4',
'portrait'
);


$dompdf->render();



$pdf=$dompdf->output();




// ===============================
// MOSTRAR PDF
// ===============================


header(
'Content-Type: application/pdf'
);


header(
'Content-Disposition: inline; filename="eliminatoria_'.$fase.'_folio_'.$folio.'.pdf"'
);


echo $pdf;

exit;

?>