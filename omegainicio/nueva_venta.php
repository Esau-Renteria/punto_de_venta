<?php
	session_start();
	include "../bd_conexion.php";

	
	?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<?php include "include/scripts.php"; ?>
	<title>Nueva Venta</title>
</head>
<body>
	<?php include "include/header.php"; ?>
	<section id="container">
		<div class="title_page">

			<h1><i class="fas fa-cube"></i> Nueva Venta</h1>
		</div>
		<div class="datos_cliente">
			<div class ="action_cliente">
			<h4>Datos del Cliente</h4>
			<a href ="#" class="btn_new btn_new_cliente"><i class="fas fa-plus"></i> Nuevo cliente </a>
		</div>
			<form name="form_new_cliente_venta" id="form_new_cliente_venta" class="datos">
			<input type="hidden" name="action" value="addCliente" >
			<input type="hidden" id="idcliente" name="idcliente"  value="" required>
			<div class="wd30">
				<label>Codigo</label>
				<input type="text" name="nit_cliente" id="nit_cliente">
			</div>
			<div class="wd30">
				<label>Nombre</label>
				<input type="text" name="nom_cliente" id="nom_cliente" disabled required>
			</div>
			<div class="wd30">
				<label>Telefono</label>
				<input type="number" name="tel_cliente" id="tel_cliente" disabled required>
			</div>
			<div class="wd100">
				<label>Direccion</label>
				<input type="text" name="dir_cliente" id="dir_cliente" disabled required>
			</div>
			<div id="div_registro_cliente" class="wd100">
				<button type="submit" class="btn_save"><i class="far fa-save fa-lg"></i>Guardar</button>
			</div>
	</form>
</div>
	<div class="datos_venta">
		<div class="datos">
			<div class="wd50">
				<label>Vendedor</label>
				<p><?php echo $_SESSION['nombre']; ?></p>
			</div>
			<div class="wd50">
				<label>Acciones</label>
				<div id="acciones_venta">
					<a href="#" class="btn_ok textcenter" id="btn_anular_venta"><i class="fas fa-ban"></i> Anular</a>
					<a href="#" class="btn_new textcenter" id="btn_facturar_venta" style="display: none;"><i class="far fa-edit"></i> Procesar</a>
				</div>
			</div>
		</div>
	</div>
	<table class="tbl_venta">
	<thead>
		<tr>
			<th width="100px">Codigo</th>
			<th>Descripcion</th>
			<th>Existencia</th>
			<th width="100px">Cantidad</th>
			<th class="textright">Precio</th>
			<th class="textright">Precio Total</th>
			<th> Acccion</th>
		</tr>
		<tr>
			<td><input type="text" name="txt_cod_producto" id="txt_cod_producto"></td>
			<td id="txt_descripcion">-</td>
			<td id="txt_existencia">-</td>
			<td><input type="text" name="txt_cant_producto" id="txt_cant_producto" value="0" min="1" disabled></td>
			<td id="txt_precio" class="textright">0.00</td>
			<td id="txt_precio_total" class="textright">0.00</td>
			<td> <a href="#" id="add_product_venta" class="link_add"><i class="fas fa-plus"></i> Agregar</a></td>
		</tr>
		<tr>
			<th>Codigo</th>
			<th colspan="2">Descripcion</th>
			<th>Cantidad</th>
			<th class="textright">Precio</th>
			<th class="textright">Precio Total</th>
			<th> Accion</th>
		</tr>
	</thead>
	<tbody id="detalle_venta">
		<!--Contenido Ajax -->
	</tbody>
	<tfoot id="detalle_totales">
		
	</tfoot>
</table>
	</section>

	<?php include "include/footer.php"; ?>
	<script type="text/javascript">
		$(document).ready(function(){
			var usuarioid ='<?php echo $_SESSION['idUser']; ?>';
			searchForDetalle(usuarioid);
		});
		</script>
</body>
</html>