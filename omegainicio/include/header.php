<?php 

if(empty($_SESSION['active'])){ // Si la session no esta activada lo mandamos al login
	header('location: ../');
}
 ?>
<header>
		<div class="header">
			
			<h1>ΩMEGA</h1>
			<div class="optionsBar">
				<p><?php echo "Hoy es ", fechaactual() //llamaos la funcion de la fecha ?></p> 
				<span>|</span>
				<span class="user"><?php echo "Bienvenido: ",$_SESSION['user'].' A'.$_SESSION['rol']; ?></span>
				<img class="photouser" src="img/user.png" alt="Usuario">
				<a href="salir.php"><img class="close" src="img/salir.png" alt="Salir del sistema" ><title="Salir"></a>
			</div>
		</div>
		<?php include "nav.php" ?>
	</header>
	<div class="modal">
		<div class="bodyModal">
		
	</div>
</div>