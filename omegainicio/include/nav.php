<nav>
			<ul>
				<li><a href="index.php"><i class="fas fa-home"></i> Inicio</a></li>
				<?php
						if($_SESSION['rol'] == 1){
							
						
						?>
				<li class="principal">

					<a href="#"><i class="far fa-user"></i> Usuarios</a>
					<ul>
						<li><a href="registro_usuario.php"><i class="fas fa-user-plus"></i> Nuevo Usuario</a></li>
						<li><a href="lista_usuarios.php"><i class="fas fa-users"></i> Lista de Usuarios</a></li>
					</ul>
				</li>
			<?php } ?>
				<li class="principal">
					<a href="#"><i class="far fa-user"></i> Clientes</a>
					<ul>
						<li><a href="registro_cliente.php"><i class="fas fa-user-plus"></i>Nuevo Cliente</a></li>
						<li><a href="lista_clientes.php"><i class="fas fa-users"></i> Lista de Clientes</a></li>
					</ul>
				</li>
				<?PHP
				if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2 ){
							
						
						?>
				<li class="principal">

					<a href="#"><i class="far fa-user"></i> Proveedores</a>
					<ul>
						<li><a href="registro_provedor.php"><i class="fas fa-user-plus"></i> Nuevo Proveedor</a></li>
						<li><a href="lista_proveedor.php"><i class="fas fa-users"></i> Lista de Proveedores</a></li>
					</ul>
				</li>
			<?php } ?>
				<li class="principal">
					<a href="#"><i class="far fa-user"></i> Productos</a>
					<ul>
						<?php
						if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2 ){
							
						
						?>
						<li><a href="registro_producto.php"><i class="fas fa-user-plus"></i> Nuevo Producto</a></li>
					<?php } ?>
						<li><a href="lista_producto.php"><i class="fas fa-cube"></i> Lista de Productos</a></li>
						<li><a href="excel_producto.php"><i class="fas fa-cube"></i> Lista de Productos EXCEL</a></li>
					</ul>
				</li>
				<li class="principal">
					<a href="#"><i class="far fa-user"></i> Ventas</a>
					<ul>
						<li><a href="nueva_venta.php"><i class="far fa-user"></i> Nueva Venta</a></li>
						<li><a href="ventas.php"><i class="far fa-user"></i> Ventas</a></li>
					</ul>
				</li>
			</ul>
		</nav>