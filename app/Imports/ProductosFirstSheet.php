<?php
/**
 * Created by PhpStorm.
 * User: Luciano
 * Date: 29/12/2021
 * Time: 18:58
 */

namespace sysfact\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use sysfact\Categoria;
use sysfact\Http\Controllers\ProductoController;
use sysfact\Producto;
use sysfact\Ubicacion;


class ProductosFirstSheet implements ToCollection, WithHeadingRow
{
    public function headingRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row)
        {
            if($row['nombre']==''){
                break;
            }

            //Verificar unidad de medida
            switch (strtoupper($row['unidad_de_medida'])){
                case 'METRO':
                    $row['unidad_de_medida']='MTR/M';
                    break;
                case 'ROLLO':
                    $row['unidad_de_medida']='RO/ROL';
                    break;
                case 'KILOGRAMO':
                    $row['unidad_de_medida']='KGM/KG';
                    break;
                case 'GRAMO':
                    $row['unidad_de_medida']='GRM/G';
                    break;
                case 'LITRO':
                    $row['unidad_de_medida']='LTR/L';
                    break;
                case 'PIEZA':
                    $row['unidad_de_medida']='NIU/PZA';
                    break;
                case 'METRO CUADRADO':
                    $row['unidad_de_medida']='MTK/M2';
                    break;
                case 'METRO CUBICO':
                    $row['unidad_de_medida']='MTQ/M3';
                    break;
                case 'PAQUETE':
                    $row['unidad_de_medida']='PK/PQ';
                    break;
                case 'CAJA':
                    $row['unidad_de_medida']='BX/CJ';
                    break;
                case 'JUEGO':
                    $row['unidad_de_medida']='NIU/JG';
                    break;
                case 'PAR':
                    $row['unidad_de_medida']='NIU/PR';
                    break;
                case 'FARDO':
                    $row['unidad_de_medida']='BE/BE';
                    break;
                case 'BOLSA':
                    $row['unidad_de_medida']='BG/BG';
                    break;
                case 'BALDE':
                    $row['unidad_de_medida']='BJ/BJ';
                    break;
                default:
                    $row['unidad_de_medida']='NIU/UND';
            }

            //Verificar campos vacíos

            if($row['categoria']==''){
                $row['categoria']='GENERAL';
            }

            if($row['costo']==''){
                $row['costo']=0.00;
            }

            if($row['precio']==''){
                $row['precio']=0.00;
            }

            if($row['stock_bajo']==''){
                $row['stock_bajo']=0;
            }

            if($row['moneda']==''){
                $row['moneda']='PEN';
            }

            if($row['codigo']==''){
                $producto=new ProductoController();
                $row['codigo']=$producto->generar_codigo_producto();
            }

            //Verificar categoria
            $categorias=Categoria::all();
            $idcategoria=-1;
            foreach ($categorias as $categoria){
                if(mb_strtoupper($row['categoria'])==mb_strtoupper($categoria['nombre'])){
                    $idcategoria=$categoria['idcategoria'];
                    break;
                }
            }
            //Si la categoría no existe la creamos
            if($idcategoria==-1){
                $categoria = new Categoria();
                $categoria->nombre=mb_strtoupper($row['categoria']);
                $categoria->save();
                $idcategoria=$categoria->idcategoria;
            }

            if($row['ubicacion']==''){
                $idubicacion=1;
            } else{
                //Verificar ubicacion
                $ubicacion=Ubicacion::where('idalmacen',1)->get();
                $idubicacion=-1;
                foreach ($ubicacion as $item){
                    if(mb_strtoupper($row['ubicacion'])==mb_strtoupper($item['nombre'])){
                        $idubicacion=$item['idubicacion'];
                        break;
                    }
                }
                //Si la ubicacion no existe la creamos
                if($idubicacion==-1){
                    $ubicacion = new Ubicacion();
                    $ubicacion->idalmacen=1;
                    $ubicacion->nombre=mb_strtoupper($row['ubicacion']);
                    $ubicacion->eliminado=0;
                    $ubicacion->save();
                    $idubicacion=$ubicacion->idubicacion;
                }
            }

            if($row['cantidad']<=0 || $row['cantidad']==''){
                $tipo_producto = 2;
            } else {
                $tipo_producto = 1;
            }

            if($row['cantidad']==''){
                $row['cantidad']=0;
            }

            if($row['stock_bajo']==''){
                $row['stock_bajo']=0;
            }

            if($row['moneda_costo']==''){
                $row['moneda_costo']='PEN';
            }

            if($row['tipo_de_cambio']==''){
                $row['tipo_de_cambio']=1;
            }

            if($row['precio_min']==''){
                $row['precio_min']=0.00;
            }

            if($row['moneda_precio_min']==''){
                $row['moneda_precio_min']='PEN';
            }

            $producto=Producto::create([
                'cod_producto'=> strtoupper($row['codigo']),
                'nombre'=>mb_strtoupper($row['nombre']),
                'presentacion'=>mb_strtoupper($row['caracteristicas']),
                'costo'=>$row['costo'],
                'precio'=>$row['precio'],
                'stock_bajo'=>$row['stock_bajo'],
                'idcategoria'=>$idcategoria,
                'unidad_medida'=>$row['unidad_de_medida'],
                'moneda'=>strtoupper($row['moneda']),
                'tipo_producto'=>$tipo_producto,
                'marca'=>mb_strtoupper($row['marca']),
                'modelo'=>mb_strtoupper($row['modelo']),
                'param_1'=>mb_strtoupper($row['montaje']),
                'param_2'=>mb_strtoupper($row['capsula']),
                'param_3'=>mb_strtoupper($row['tipo']),
                'param_4'=>mb_strtoupper($row['precio_min']),
                'param_5'=>mb_strtoupper($row['moneda_precio_min']),
            ]);

            $producto->inventario()->create([
                'idempleado'=>auth()->user()->idempleado,
                'cantidad'=>$row['cantidad'],
                'saldo'=>$row['cantidad'],
                'operacion'=>'IMPORTADO DESDE EXCEL',
                'costo'=>$row['costo'],
                'moneda' => strtoupper($row['moneda_costo']),
                'tipo_cambio' => $row['tipo_de_cambio'],
            ]);



            $producto->almacen()->attach(1, [
                'idubicacion'=>$idubicacion,
            ]);

        }
    }
}