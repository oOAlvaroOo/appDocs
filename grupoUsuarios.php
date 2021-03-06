<?php session_start(); ?>

<!DOCTYPE html>
<html lang="es">
<head>
	<!-- <meta http-equiv="Content-Type" content="text/html; charset=utf-8"> -->
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
	<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
	<script src="JQ/jquery.min.js"></script>
	<script src="bootstrap/js/bootstrap.min.js"></script>
	<script src="bootstrap/js/bootbox.min.js"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<title>Administracion Grupos y Usuarios</title>
	<style>
	.glyphicon-remove{
		color: #D50000;
	}
	.glyphicon-ok{
		color: green;
	}
	</style>
	<script>
	$(document).ready(abrirDialogAgregarGrupo);
	$(document).ready(abrirDialogModificarGrupo);
	$(document).ready(abrirDialogAgregarUsuario);
	$(document).ready(cargarGrupoSelect('php/consultaGruposUsuario.php', '#selectGrupoUsuario'));
	//$(document).ready(cargarSubGrupoSelect('php/consultaDisciplinas.php', '#selectGrupoDisciplina2', '#selectDisciplina2'));
	// $(document).ready(cargarSubGrupoSelect('php/consultaDisciplinas.php', '#selectGrupoDisciplina2', '#selectDisciplina2'));
	$(document).ready(eliminarGrupo);
	$(document).ready(buscarUsuarios);
	$(document).ready(abrirDialogModificarUsuario);

	// FUNCION QUE CARGA SOLO LOS SELECT QUE SON DE GRUPOS (AREAS, GRUPO DISCIPLINA, GRUPO TIPO DOC, GRUPO USUARIO)
	function cargarGrupoSelect(rutaPHP, contenedor){
		$.ajax({
			url: rutaPHP,
			type: 'POST',
			dataType: 'json',
			error: function(jqXHR,text_status,strError){
				alert("No hay coneccion");
			},
			timeout: 60000,
			success: function(data){
				$(contenedor).html('');
				for(var i in data){
					$(contenedor).append("<option value='" + data[i][0] + "'>" + data[i][1] + "</option>");
				}
			}
		});
	};
	//FUNCION QUE CARGA SOLO LOS SELECT QUE SON DE SUB GRUPOS (SUB AREAS, DISCIPLINA, TIPO DOC, USUARIO)
	function cargarDisciplinas(){
		var optionSeleccionado = 'cualquier';
		$.ajax({
			data: {optionSeleccionadoPHP: optionSeleccionado},
			url: 'php/consultaDisciplinas.php',
			type: 'POST',
			dataType: 'json',
			error: function(jqXHR,text_status,strError){
				alert("Error al carga las disciplinas");
			},
			timeout: 60000,
			success: function(data){
				$('#selectDisciplina2').html("");
				for(var i in data){
					$('#selectDisciplina2').append("<option value='" + data[i][0] + "'>" + data[i][1] + "</option>");
				}
			}
		});
	};
	function abrirDialogAgregarGrupo(){
		$('#btnAgregarGrupo').click(function(){
			bootbox.dialog({
                title: "Agregar Grupo de Usuarios",
                message: '<div class="row">  ' +
                    '<div class="col-md-12"> ' +
                    '<form class="form-horizontal"> ' +
                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="name">Nombre Grupo</label> ' +
                    '<div class="col-md-4"> ' +
                    '<input id="inpNombreGrupo" name="inpNombreGrupo" type="text" placeholder="Nombre del grupo" class="form-control input-md"> ' +
                    '</div> </div>' +
                    '</form> </div></div>',
                buttons: {
                    primary: {
                        label: "Agregar",
                        className: "btn-primary",
                        callback: function () {
                            agregarGrupoUsuario();
                        }
                    }
                }
            }
        );
		});
	};

	function agregarGrupoUsuario(){
		var nombreGrupo = $.trim($('#inpNombreGrupo').val());
		if (nombreGrupo != '') {
			$.ajax({
				data: ({nombreGrupoPHP: nombreGrupo}),
				url: 'php/agregarGrupoUsuario.php',
				type: 'POST',
				error: function(jqXHR,text_status,strError){
					alert("Error al agregar el grupo: \n\n" + strError);
				},
				timeout: 60000,
				success: function(data){
					alert('SE HA AGREGADO EL GRUPO');

					//agrega al log la acccion de agregado de grupo
					var accionLog = 'Ha agregado el grupo ' + nombreGrupo;
					var codUsuarioLog = <?php echo $_SESSION['codUsuario'] ?>;

					// alert('accion:' + accionLog + ' codigo Usuario: ' + codUsuarioLog + ' cod Doc: ' + codDoc);
					$.ajax({
						data: ({accionLogPHP: accionLog, codUsuarioLogPHP: codUsuarioLog}),
						type: "POST",
						url: "php/agregarLog.php",
						cache: false
					});

					$('#selectGrupoUsuario').html('');
					cargarGrupoSelect('php/consultaGruposUsuario.php', '#selectGrupoUsuario');
				}
			});
		};
	};

	function elimminarGrupoUsuario(){
		var nombreGrupoAModificar = $('#selectGrupoUsuario option:selected').text();
		var codGrupo = $('#selectGrupoUsuario').val();
		$.ajax({
			data: ({codGrupoPHP: codGrupo}),
			url: 'php/eliminarGrupoUsuario.php',
			type: 'POST',
			error: function(jqXHR,text_status,strError){
				alert("Error al aliminar el grupo: \n\n" + strError);
			},
			timeout: 60000,
			success: function(data){
				alert('SE HA ELIMINADO EL GRUPO ' + nombreGrupoAModificar);

				//agrega al log la acccion de eliminado de grupo
				var accionLog = 'Ha eliminado el grupo ' + nombreGrupoAModificar;
				var codUsuarioLog = <?php echo $_SESSION['codUsuario'] ?>;

				// alert('accion:' + accionLog + ' codigo Usuario: ' + codUsuarioLog + ' cod Doc: ' + codDoc);
				$.ajax({
					data: ({accionLogPHP: accionLog, codUsuarioLogPHP: codUsuarioLog}),
					type: "POST",
					url: "php/agregarLog.php",
					cache: false
				});

				$('#selectGrupoUsuario').html('');
				cargarGrupoSelect('php/consultaGruposUsuario.php', '#selectGrupoUsuario');
			}
		});
	};

	function eliminarGrupo(){
		$('#btnEliminarGrupo').click(function(){
			bootbox.confirm('¿Realmente desea eliminar el grupo de usuarios?', function(result) {
				if(result){
					elimminarGrupoUsuario();
					$('#btnEliminarGrupo').click(cargarGrupoSelect('php/consultaGruposUsuario.php', '#selectGrupoUsuario'));
				}
			});
		});
	};

	function abrirDialogModificarGrupo(){
		$('#btnModificarGrupo').click(function(){
			bootbox.dialog({
                title: "Modificar Grupo de Usuarios",
                message: '<div class="row">  ' +
                    '<div class="col-md-12"> ' +
                    '<form class="form-horizontal"> ' +
                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="name">Nuevo Nombre Grupo</label> ' +
                    '<div class="col-md-4"> ' +
                    '<input id="inpNuevoNombreGrupo" name="inpNuevoNombreGrupo" type="text" placeholder="Nuevo nombre del grupo" class="form-control input-md"> ' +
                    '</div> </div>' +
                    '</form> </div></div>',
                buttons: {
                    primary: {
                        label: "Modificar",
                        className: "btn-primary",
                        callback: function () {
                            modificarGrupoUsuario();
                        }
                    }
                }
            }
        );
		});
	};
	function modificarGrupoUsuario(){
		var nuevoNombreGrupo = $.trim($('#inpNuevoNombreGrupo').val());
		var codGrupo = $('#selectGrupoUsuario').val();
		$.ajax({
			data: ({nuevoNombreGrupoPHP: nuevoNombreGrupo, codGrupoPHP: codGrupo}),
			url: 'php/modificarGrupoUsuario.php',
			type: 'POST',
			error: function(jqXHR,text_status,strError){
				alert("Error al modificar el grupo: \n\n" + strError);
			},
			timeout: 60000,
			success: function(data){
				alert('SE HA MODIFICADO EL GRUPO');

				//agrega al log la acccion de modificado de grupo
				var accionLog = 'Ha modificado el grupo ' + nuevoNombreGrupo;
				var codUsuarioLog = <?php echo $_SESSION['codUsuario'] ?>;

				// alert('accion:' + accionLog + ' codigo Usuario: ' + codUsuarioLog + ' cod Doc: ' + codDoc);
				$.ajax({
					data: ({accionLogPHP: accionLog, codUsuarioLogPHP: codUsuarioLog}),
					type: "POST",
					url: "php/agregarLog.php",
					cache: false
				});

				$('#selectGrupoUsuario').html('');
				cargarGrupoSelect('php/consultaGruposUsuario.php', '#selectGrupoUsuario');
			}
		});
	};
	function buscarUsuarios(){
		$.ajax({
			url: 'php/consultaListadoUsuarios.php',
			type: 'POST',
			dataType: 'json',
			error: function(jqXHR,text_status,strError){
				alert("Error al cargar los usuarios: \n\n" + strError);
			},
			timeout: 60000,
			success: function(data){
				$('#cuerpoTabla').html("");
				for(var i in data){
					$('#cuerpoTabla').append("<tr><td>" + data[i][0] + "</td><td>" + data[i][1] + "</td><td>" + data[i][2] + "</td><td>" + data[i][3] + "</td><td align='center'><span class='" + data[i]['rolAdministrador'] + "'></span></td><td align='center'><span class='" + data[i]['permisoAgregarDoc'] + "'></span></td><td align='center'><span class='" + data[i]['PermisoBuscarVerDoc'] + "'></span></td><td><button type='button' id='" + data[i][8] + "' class='btn btn-info btnEditarUsuario'>Editar</button></td></tr>");
				}
				//SIEMPRE AL FINAL DE LA TABLA SE AGREGA EL BOTON 'NUEVO USUARIO'
				$('#cuerpoTabla').append("<tr><td><a id='btnAgregarUsuario' href='#' class='btn btn-primary btnAgregarUsuario' role='button'>Nuevo usuario</a></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>");
			}
		});
	};

	function abrirDialogAgregarUsuario(){
		cargarGrupoSelect('php/consultaGruposUsuario.php', '#selectGrupoUsuario');
		$('#tabla').on('click', '.btnAgregarUsuario', function(){
			cargarGrupoSelect('php/consultaGruposUsuario.php', '#selectGrupoUsuario2');
			cargarDisciplinas();
			// solo con ajax cambia atributos de boton
			$.ajax({
				success: function(){
					$('button[data-bb-handler="btnAgregarUsuario2"]').attr('disabled', true);
					//cuando gana y pierde el foco y cuando se hace click  se verifica si todos los campos estan rellenos
					$('#inpNombreUsuario').click(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpNombrePersona').click(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpApellidoPersona').click(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpEmail').click(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpClave').click(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#selectGrupoUsuario2').click(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#selectDisciplina2').click(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpNombreUsuario').focus(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpNombrePersona').focus(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpApellidoPersona').focus(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpEmail').focus(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpClave').focus(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#selectGrupoUsuario2').focus(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#selectDisciplina2').focus(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpNombreUsuario').focusout(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpNombrePersona').focusout(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpApellidoPersona').focusout(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpEmail').focusout(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#inpClave').focusout(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#selectGrupoUsuario2').focusout(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
					$('#selectDisciplina2').focusout(function() {
						desbloqueaAgregarUsuarioCuandoEstaTodoRellenado();
					});
				}
			});
			bootbox.dialog({
				title: "Agregar Usuario",
				message: '<div class="row">  ' +
                    '<div class="col-md-12"> ' +
                    '<form class="form-horizontal"> ' +

                    //label con input
                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="inpNombreUsuario">Nombre Usuario</label> ' +
                    '<div class="col-md-6"> ' +
                    '<input id="inpNombreUsuario" name="inpNombreUsuario" type="text" placeholder="Nombre del usuario" class="form-control input-md"> ' +
                    '</div> </div>' +
                    //termina label con input

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="inpNombrePersona">Nombre Persona</label> ' +
                    '<div class="col-md-6"> ' +
                    '<input id="inpNombrePersona" name="inpNombrePersona" type="text" placeholder="Nombre de la persona" class="form-control input-md"> ' +
                    '</div> </div>' +

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="inpApellidoPersona">Apellido Persona</label> ' +
                    '<div class="col-md-6"> ' +
                    '<input id="inpApellidoPersona" name="inpApellidoPersona" type="text" placeholder="Apellido de la persona" class="form-control input-md"> ' +
                    '</div> </div>' +

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="inpEmail">Email</label> ' +
                    '<div class="col-md-6"> ' +
                    '<input id="inpEmail" name="inpEmail" type="text" placeholder="Email del usuario" class="form-control input-md"> ' +
                    '</div> </div>' +

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="inpClave">Clave</label> ' +
                    '<div class="col-md-6"> ' +
                    '<input id="inpClave" name="inpClave" type="text" placeholder="Clave de ingreso" class="form-control input-md"> ' +
                    '</div> </div>' +

                    '<div class="form-group"> ' +
						'<label for="selectGrupoUsuario2" class="col-md-4 control-label">Grupo usuario</label>' +
						'<div class="col-md-6"> ' +
							'<select id="selectGrupoUsuario2" name="selectGrupoUsuario2" class="form-control">' +
							'</select>' +
						'</div>' +
					'</div>' +

					'<div class="form-group"> ' +
						'<label for="selectDisciplina2" class="col-md-4 control-label">Disciplina</label>' +
						'<div class="col-md-6"> ' +
							'<select id="selectDisciplina2" name="selectDisciplina2" class="form-control" multiple="multiple" style="height: 200px">' +
							'</select>' +
							'<span class="help-block">Nota: Para seleccionar varias disciplinas, debe presionar CTRL mientras hace click sobre una disciplina</span>' +
						'</div>' +
					'</div>' +

					'<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="checkAdm">Administrador</label> ' +
                    '<div class="col-md-1"> ' +
						'<input id="checkAdm" name="checkAdm" type="checkbox"> ' +
                    '</div>' +

                    '<label class="col-md-4 control-label" for="checkAgregarDoc">Permiso agregar documentos</label> ' +
                    '<div class="col-md-1"> ' +
                    '<input id="checkAgregarDoc" name="checkAgregarDoc" type="checkbox"> ' +
                    '</div> </div>' +

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="checkBuscarVerDoc">Permiso buscar y ver documentos</label> ' +
                    '<div class="col-md-1"> ' +
                    '<input id="checkBuscarVerDoc" name="checkBuscarVerDoc" type="checkbox"> ' +
                    '</div>' +

                    '</form> </div></div>',
                buttons: {
                    btnAgregarUsuario2: {
                        label: "Agregar",
                        className: "btn-primary",
                        callback: function (e) {
							agregarUsuario();
                        }
                    }
                }
            });
		});
	};

	//FUNCION PARA AGREGAR USUARIO
	function agregarUsuario(){
		var nombreUsuario = $.trim($('#inpNombreUsuario').val());
		var nombrePersona = $.trim($('#inpNombrePersona').val());
		var apellidoPersona = $.trim($('#inpApellidoPersona').val());
		var emailPersona = $.trim($('#inpEmail').val());
		var claveUsuario = $('#inpClave').val();
		var grupoUsuarioAsignado = $('#selectGrupoUsuario2').val();
		if ($('#checkAdm').prop('checked')) {var rolAdm = 1;} else {var rolAdm = 0;}
		if ($('#checkAgregarDoc').prop('checked')) {var permisoAgregarDoc = 1;} else {var permisoAgregarDoc = 0;}
		if ($('#checkBuscarVerDoc').prop('checked')) {var permisoBuscarVerDoc = 1;} else {var permisoBuscarVerDoc = 0;}
		// disciplinasSeleccionadas es un array q contiene las disciplinas seleccionadas
		var disciplinasSeleccionadas = $('#selectDisciplina2').val();
		if (nombreUsuario != '' && nombrePersona != '' && apellidoPersona != '' && emailPersona != '' && claveUsuario != '' && grupoUsuarioAsignado != null && disciplinasSeleccionadas != null) {
			$.ajax({
				data: ({nombreUsuarioPHP: nombreUsuario, nombrePersonaPHP: nombrePersona, apellidoPersonaPHP: apellidoPersona, emailPersonaPHP: emailPersona, claveUsuarioPHP: claveUsuario, grupoUsuarioAsignadoPHP:grupoUsuarioAsignado, rolAdmPHP: rolAdm, permisoAgregarDocPHP: permisoAgregarDoc, permisoBuscarVerDocPHP: permisoBuscarVerDoc, disciplinasSeleccionadasPHP: disciplinasSeleccionadas}),
				url: 'php/agregarUsuario.php',
				type: 'POST',
				error: function(jqXHR,text_status,strError){
					alert("Error al agregar el usuario: \n\n" + strError);
				},
				timeout: 60000,
				success: function(data){
					alert(data);

					//agrega al log la acccion de agregado de usuario
					var accionLog = 'Ha agregado el usuario ' + nombreUsuario;
					var codUsuarioLog = <?php echo $_SESSION['codUsuario'] ?>;

					// alert('accion:' + accionLog + ' codigo Usuario: ' + codUsuarioLog + ' cod Doc: ' + codDoc);
					$.ajax({
						data: ({accionLogPHP: accionLog, codUsuarioLogPHP: codUsuarioLog}),
						type: "POST",
						url: "php/agregarLog.php",
						cache: false
					});

					$('#cuerpoTabla').html('');
					buscarUsuarios();
				}
			});
		}else{
			alert('FALTAN DATOS');
		}
	};
	function desbloqueaAgregarUsuarioCuandoEstaTodoRellenado(){
		if ($('#inpNombreUsuario').val() != '' &&
			$('#inpNombrePersona').val() != '' &&
			$('#inpApellidoPersona').val() != '' &&
			$('#inpEmail').val() != '' &&
			$('#inpClave').val() != '' &&
			$('#selectGrupoUsuario2').val() != null &&
			$('#selectDisciplina2').val() != null) {

			$('button[data-bb-handler="btnAgregarUsuario2"]').attr('disabled', false);

		}else{
			$('button[data-bb-handler="btnAgregarUsuario2"]').attr('disabled', true);
		}
	};
	var codGrupoUsuarioAnterior, nombreUsuarioAnterior;
	function abrirDialogModificarUsuario(){
		$('#tabla').on('click', '.btnEditarUsuario', function(){
			cargarGrupoSelect('php/consultaGruposUsuario.php', '#selectGrupoUsuario3');
			var codUsuarioSeleccionadoAEditar = $(this).attr("id");
			//alert(codUsuarioSeleccionadoAEditar);
			$.ajax({
				data: {codUsuarioSeleccionadoAEditarPHP: codUsuarioSeleccionadoAEditar},
				url: 'php/consultaDatosUsuarioAModificar.php',
				type: 'POST',
				dataType: 'json',
				error: function(jqXHR,text_status,strError){
					alert("Error al cargar los datos del usuario: \n\n" + strError);
				},
				timeout: 60000,
				success: function(data){
					$('#inpNombreUsuario2').val(data[0][0]);
					$('#inpNombrePersona2').val(data[0][1]);
					$('#inpApellidoPersona2').val(data[0][2]);
					$('#inpEmail2').val(data[0][3]);
					$('#inpClave2').val(data[0][4]);
					if (data[0][5] == "1") {$("#checkAdm2").prop("checked", true);};
					if (data[0][6] == "1") {$("#checkAgregarDoc2").prop("checked", true);};
					if (data[0][7] == "1") {$("#checkBuscarVerDoc2").prop("checked", true);};
					if (data[0][8] == "1") {$("#checkUsuarioHabilitado").prop("checked", true);};

					if (codUsuarioSeleccionadoAEditar == 1) {
						$('#selectGrupoUsuario3').attr('disabled', true);
						$('#checkAdm2').attr('disabled', true);
						$('#checkAgregarDoc2').attr('disabled', true);
						$('#checkBuscarVerDoc2').attr('disabled', true);
						$('#checkUsuarioHabilitado').attr('disabled', true);
						$('button[data-bb-handler="btnEliminarUsuario"]').attr('disabled', true);
						$('button[data-bb-handler="btnEliminarUsuario"]').css("display","none");
					};
					nombreUsuarioAnterior = data[0][0]
					codGrupoUsuarioAnterior = data[0][9];
				}
			});
			bootbox.dialog({
                title: "Modificar Usuario",
                message: '<div class="row">  ' +
                    '<div class="col-md-12"> ' +
                    '<form class="form-horizontal"> ' +

                    //label con input
                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="inpNombreUsuario">Nombre Usuario</label> ' +
                    '<div class="col-md-6"> ' +
                    '<input id="inpNombreUsuario2" name="inpNombreUsuario" type="text" placeholder="Nombre del usuario" class="form-control input-md"> ' +
                    '</div> </div>' +
                    //termina label con input

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="inpNombrePersona">Nombre Persona</label> ' +
                    '<div class="col-md-6"> ' +
                    '<input id="inpNombrePersona2" name="inpNombrePersona" type="text" placeholder="Nombre de la persona" class="form-control input-md"> ' +
                    '</div> </div>' +

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="inpApellidoPersona">Apellido Persona</label> ' +
                    '<div class="col-md-6"> ' +
                    '<input id="inpApellidoPersona2" name="inpApellidoPersona" type="text" placeholder="Apellido de la persona" class="form-control input-md"> ' +
                    '</div> </div>' +

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="inpEmail">Email</label> ' +
                    '<div class="col-md-6"> ' +
                    '<input id="inpEmail2" name="inpEmail" type="text" placeholder="Email del usuario" class="form-control input-md"> ' +
                    '</div> </div>' +

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="inpClave">Clave</label> ' +
                    '<div class="col-md-6"> ' +
                    '<input id="inpClave2" name="inpClave" type="text" placeholder="Clave de ingreso" class="form-control input-md"> ' +
                    '</div> </div>' +

                    '<div class="form-group"> ' +
						'<label for="selectGrupoUsuario3" class="col-md-4 control-label">Grupo usuario</label>' +
						'<div class="col-md-6"> ' +
							'<select id="selectGrupoUsuario3" name="selectGrupoUsuario3" class="form-control">' +
							'</select>' +
						'</div>' +
					'</div>' +

					'<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="checkAdm">Administrador</label> ' +
                    '<div class="col-md-1"> ' +
						'<input id="checkAdm2" name="checkAdm" type="checkbox"> ' +
                    '</div>' +

                    '<label class="col-md-4 control-label" for="checkAgregarDoc">Permiso agregar documentos</label> ' +
                    '<div class="col-md-1"> ' +
                    '<input id="checkAgregarDoc2" name="checkAgregarDoc" type="checkbox"> ' +
                    '</div> </div>' +

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="checkBuscarVerDoc">Permiso buscar y ver documentos</label> ' +
                    '<div class="col-md-1"> ' +
                    '<input id="checkBuscarVerDoc2" name="checkBuscarVerDoc" type="checkbox"> ' +
                    '</div>' +

                    '<div class="form-group"> ' +
                    '<label class="col-md-4 control-label" for="checkUsuarioHabilitado">Habilitado</label> ' +
                    '<div class="col-md-1"> ' +
                    '<input id="checkUsuarioHabilitado" name="checkUsuarioHabilitado" type="checkbox"> ' +
                    '</div> </div>' +

                    '</form> </div></div>',
                buttons: {
                    btnModificarUsuario: {
                        label: "Modificar",
                        className: "btn-success",
                        callback: function () {
							var nombreUsuarioNuevo = $('#inpNombreUsuario2').val();
							var nombrePersonaNuevo = $('#inpNombrePersona2').val();
							var apellidoPersonaNuevo = $('#inpApellidoPersona2').val();
							var emailPersonaNuevo = $('#inpEmail2').val();
							var claveUsuarioNuevo = $('#inpClave2').val();

							// alert(codUsuarioSeleccionadoAEditar);
							// alert($('#inpNombreUsuario2').val());
							//`alert(codGrupoUsuarioAnterior);
							// alert(nombreUsuarioNuevo);
							// alert(nombreUsuarioAnterior);

							var grupoUsuarioAsignadoNuevo = $('#selectGrupoUsuario3').val();
							//alert(grupoUsuarioAsignadoNuevo);
							if ($('#checkAdm2').prop('checked')) {var rolAdmNuevo = 1;} else {var rolAdmNuevo = 0;}
							if ($('#checkAgregarDoc2').prop('checked')) {var permisoAgregarDocNuevo = 1;} else {var permisoAgregarDocNuevo = 0;}
							if ($('#checkBuscarVerDoc2').prop('checked')) {var permisoBuscarVerDocNuevo = 1;} else {var permisoBuscarVerDocNuevo = 0;}
							if ($('#checkUsuarioHabilitado').prop('checked')) {var estado = 1;} else {var estado = 0;}
							$.ajax({
								data: ({codUsuarioSeleccionadoAEditarPHP: codUsuarioSeleccionadoAEditar, nombreUsuarioAnteriorPHP: nombreUsuarioAnterior, codGrupoUsuarioAnteriorPHP: codGrupoUsuarioAnterior, nombreUsuarioPHP: nombreUsuarioNuevo, nombrePersonaPHP: nombrePersonaNuevo, apellidoPersonaNuevoPHP: apellidoPersonaNuevo, emailPersonaPHP: emailPersonaNuevo, claveUsuarioPHP: claveUsuarioNuevo, grupoUsuarioAsignadoNuevoPHP: grupoUsuarioAsignadoNuevo, rolAdmNuevoPHP: rolAdmNuevo, permisoAgregarDocNuevoPHP: permisoAgregarDocNuevo, permisoBuscarVerDocNuevoPHP: permisoBuscarVerDocNuevo, estadoPHP: estado}),
								url: 'php/modificarDatosUsuario.php',
								type: 'POST',
								dataType: 'text',
								error: function(jqXHR,text_status,strError){
									alert("Error al modificar los datos del usuario: \n\n" + strError);
								},
								timeout: 60000,
								success: function(data){
									alert(data);

									//agrega al log la acccion de modificado de usuario
									var accionLog = 'Ha modificado el usuario ' + nombreUsuarioNuevo;
									var codUsuarioLog = <?php echo $_SESSION['codUsuario'] ?>;

									// alert('accion:' + accionLog + ' codigo Usuario: ' + codUsuarioLog + ' cod Doc: ' + codDoc);
									$.ajax({
										data: ({accionLogPHP: accionLog, codUsuarioLogPHP: codUsuarioLog}),
										type: "POST",
										url: "php/agregarLog.php",
										cache: false
									});

									buscarUsuarios();
								}
							});
                        }
                    },
                    btnEliminarUsuario: {
                        label: "Eliminar usuario",
                        className: "btn-danger",
                        callback: function () {
							var codUsuarioAEliminar = codUsuarioSeleccionadoAEditar;
							var nombreUsuarioAEliminar = nombreUsuarioAnterior;
							// alert(codUsuarioAEliminar);
							// alert(nombreUsuarioAEliminar);
							bootbox.confirm("¿Realmente desea eliminar el usuario '" + nombreUsuarioAEliminar + "'?", function(result) {
								if(result){
									$.ajax({
										data: ({codUsuarioAEliminarPHP: codUsuarioAEliminar, nombreUsuarioAEliminarPHP: nombreUsuarioAEliminar}),
										url: 'php/eliminarUsuario.php',
										type: 'POST',
										dataType: 'text',
										error: function(jqXHR,text_status,strError){
											alert("Error al eliminar los datos del usuario: \n\n" + strError);
										},
										timeout: 60000,
										success: function(data){
											alert(data);

											//agrega al log la acccion de eliminado de usuario
											var accionLog = 'Ha eliminado el usuario ' + nombreUsuarioAEliminar;
											var codUsuarioLog = <?php echo $_SESSION['codUsuario'] ?>;

											// alert('accion:' + accionLog + ' codigo Usuario: ' + codUsuarioLog + ' cod Doc: ' + codDoc);
											$.ajax({
												data: ({accionLogPHP: accionLog, codUsuarioLogPHP: codUsuarioLog}),
												type: "POST",
												url: "php/agregarLog.php",
												cache: false
											});

											buscarUsuarios();
										}
									});
								}
							});
						}
					}
				}
            });
		});
	};
	</script>
</head>
<body>
	<div class="row" >
		<div class="col-md-12 col-lg-offset-2" >
			<form action="" class="form-horizontal" role="form">
				<div class="form-group">
					<div class="col-xs-2">
						<label for="" class="control-label">Grupo usuario</label>
					</div>
					<div class="col-xs-2">
						<select id="selectGrupoUsuario" class="form-control">
						</select>
					</div>
					<a id='btnAgregarGrupo' class="btn btn-primary" href="#" role="button">Agregar</a>
					<a id='btnModificarGrupo' class="btn btn-info" href="#" role="button">Modificar</a>
					<a id = "btnEliminarGrupo" class="btn btn-danger" href="#" role="button">Eliminar</a>
				</div>
			</form>
		</div>
	</div>
	<div class="row" >
		<div class="col-md-12 " >
			<table id="tabla" class="table table-hover">
				<thead>
					<tr>
						<th>Usuario</th>
						<th>Nombre</th>
						<th>Apellido</th>
						<th>Email</th>
						<th>Administrador</th>
						<th>Agregar archivos</th>
						<th>Buscar archivos</th>
						<th>Editar</th>
					</tr>
				</thead>
				<tbody id="cuerpoTabla" class="cuerpoTabla">
					<!-- <tr>
						<td>Juan</td>
						<td>Juan</td>
						<td>Perez</td>
						<td>juan@caca.cl</td>
						<td>si</td>
						<td>si</td>
						<td>no</td>
						<td><button type="button" class="btn btn-warning">Desactivar</button></td>
						<td><button type="button" class="btn btn-info">Editar</button></td>
					</tr>
					--><tr>
						<td><a href="#" class="btn btn-primary" role="button">Nuevo usuario</a></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><span id = 'spanTienePermiso' class='glyphicon glyphicon-ok'></span></td>
						<td><span id = 'spanNoTienePermiso' class='glyphicon glyphicon-remove'></span></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<!-- </div> -->
</body>
</html>

<!-- <table class="table">
  ...
</table>
 -->