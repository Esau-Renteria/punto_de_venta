<?php
session_start();


	include "../bd_conexion.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<?php include "include/scripts.php" ?>
	<title>Lista de Productos</title>
</head>
<body>
	<?php include "include/header.php" ?>
	<section id="container">

		<h1><i class="fas fa-cube"></i> Lista de Productos</h1>
		<a href="registro_producto.php" class="btn_new"><i class="fas fa-user-plus"></i> Registrar Producto </a>

		<form action="buscar_productos.php" method="get" class="form_search">
			<input type="text" name="busqueda" id="busqueda" placeholder="Buscar">
			<button type="submit" value="Buscar" class="btn_search"><i class="fas fa-search"></i></button>
		</form>
		<table>
			<tr>
				<th>Codigo</th>
				<th>Descripciom</th>
				<th>Precio</th>
				<th>Existencia</th>
				<th>
					<?PHP
						$query_proveedor = mysqli_query($conection,"SELECT codproveedor, proveedor FROM proveedor WHERE estado= 1 ORDER BY proveedor ASC");
				$result_proveedor = mysqli_num_rows($query_proveedor);
				
			 ?>
				<select name="proveedor" id="search_proveedor">
					<option value="" selected>Proveedor</option>
					<?php 	
						if($result_proveedor > 0){
							while ($proveedor = mysqli_fetch_array($query_proveedor)) {
								# code...
								?>
									<option value="<?php echo $proveedor['codproveedor'] ?>"><?php echo $proveedor['proveedor'] ?></option>
								<?php
							}
						}
					 ?>
					
				</select>
					
				</th>
				<th>Imagen</th>
				<?php if ($_SESSION['rol'] ==1 || $_SESSION['rol'] ==2 ) {  ?>
				<th>Acciones</th>
			<?php 	} ?>
			</tr>

				<?PHP 
				//paginador
				$sql_registe = mysqli_query($conection,"SELECT COUNT(*) as total_registro FROM producto WHERE estado = 1 ");
				$result_register = mysqli_fetch_array($sql_registe);
				$total_register = $result_register['total_registro'];
				$por_pagina = 4;

				if(empty($_GET['pagina']))
				{
					$pagina =1;
				}else{
					$pagina = $_GET['pagina'];
				}
				$desde = ($pagina-1) * $por_pagina;
				$total_paginas = ceil($total_register / $por_pagina);

				$query = mysqli_query($conection,"SELECT p.codproducto, p.descripcion, p.precio, p.existencia, pr.proveedor, p.foto FROM producto p INNER JOIN proveedor pr ON p.proveedor = pr.codproveedor WHERE p.estado = 1 ORDER BY p.codproducto DESC LIMIT $desde,$por_pagina"); 
				mysqli_close($conection);//

				$result = mysqli_num_rows($query);
				if($result > 0){

				while($data = mysqli_fetch_array($query)){
					if($data['foto'] != 'img_producto.jpg'){
						$foto = 'img/uploads/'.$data['foto'];
					}else{
						$foto = 'img/'.$data['foto'];
					}
				?>

				<tr class="row<?php echo $data["codproducto"]; ?>">
				<td><?php echo $data["codproducto"] ?> </td>
				<td><?php echo $data["descripcion"] ?> </td>
				<td class ="celPrecio"><?php echo $data["precio"] ?> </td>
				<td class ="celExistencia"><?php echo $data["existencia"] ?> </td>
				<td><?php echo $data["proveedor"] ?> </td>
				<td class="img_producto"><img src="<?php echo $foto ?>" alt="<?php echo $data["descripcion"] ?>"> </td>
				<?php if ($_SESSION['rol'] ==1 || $_SESSION['rol'] ==2 ) {  ?>
				<td>
					<a class="link_add add_product" product="<?php echo $data["codproducto"]; ?>" href="#"><i class="fas fa-plus"></i> Agregar +</a>
					
					
						|
					<a class="link_edit" href="modificar_producto.php?id=<?php echo $data["codproducto"]; ?>"><i class="fas fa-edit"></i> Editar</a>
					|
					
					<a class="link_delete del_product" href="#" product="<?php echo $data["codproducto"]; ?>"><i class="fas fa-trash-alt"></i> Eliminar</a>
					<?php } ?>
				</td>
				</tr>
			<?php
			}
			}

			?>
		</table>
		<div class="paginador">
			<ul>
			<?php 
				if($pagina != 1)
				{
			 ?>
				<li><a href="?pagina=<?php echo 1; ?>"><i class="fas fa-step-backward"></i>
</a></li>
				<li><a href="?pagina=<?php echo $pagina-1; ?>"><i class="fas fa-caret-left"></i></a></li>
			<?php 
				}
				for ($i=1; $i <= $total_paginas; $i++) { 
					# code...
					if($i == $pagina)
					{
						echo '<li class="pageSelected">'.$i.'</li>';
					}else{
						echo '<li><a href="?pagina='.$i.'">'.$i.'</a></li>';
					}
				}

				if($pagina != $total_paginas)
				{
			 ?>
				<li><a href="?pagina=<?php echo $pagina + 1; ?>"><i class="fas fa-caret-right"></i></a></li>
				<li><a href="?pagina=<?php echo $total_paginas; ?> "><i class="fas fa-step-forward"></i></a></li>
			<?php } ?>
			</ul>
</div>

	</section>
	<?php include "include/footer.php" ?>
</body>
</html>