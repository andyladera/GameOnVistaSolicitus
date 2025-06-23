<?php 
require_once __DIR__ . '/../../Controllers/SolicitudController.php'; 
// El controlador ya se encarga de la sesión y de obtener los datos.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Solicitudes de Registro</title>
    <link rel="stylesheet" href="../../Public/css/styles_dashboard.css"> <!-- Asumiendo que tienes un CSS para el dashboard -->
    <style>
        .container { padding: 20px; }
        .tabs {
            display: flex;
            border-bottom: 2px solid #ccc;
            margin-bottom: 20px;
        }
        .tab-link {
            padding: 10px 20px;
            cursor: pointer;
            border: 1px solid transparent;
            border-bottom: none;
            background-color: #f1f1f1;
        }
        .tab-link.active {
            background-color: #fff;
            border-color: #ccc;
            border-bottom: 2px solid #fff;
            margin-bottom: -2px;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .actions form { display: inline-block; }
        .btn-approve { color: white; background-color: #28a745; border: none; padding: 5px 10px; cursor: pointer; }
        .btn-reject { color: white; background-color: #dc3545; border: none; padding: 5px 10px; cursor: pointer; }
        /* Estilos para el Modal */
        .modal {
            display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%;
            overflow: auto; background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%;
        }
        .close-button { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestión de Solicitudes de Registro</h1>

        <?php if ($message): ?>
            <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab-link active" onclick="openTab(event, 'pendientes')">Pendientes (<?php echo count($solicitudes_pendientes); ?>)</div>
            <div class="tab-link" onclick="openTab(event, 'aprobadas')">Aprobadas (<?php echo count($solicitudes_aprobadas); ?>)</div>
            <div class="tab-link" onclick="openTab(event, 'rechazadas')">Rechazadas (<?php echo count($solicitudes_rechazadas); ?>)</div>
        </div>

        <!-- Pestaña de Pendientes -->
        <div id="pendientes" class="tab-content active">
            <h2>Solicitudes Pendientes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Institución</th>
                        <th>RUC</th>
                        <th>Email</th>
                        <th>Fecha Solicitud</th>
                        <th>Documento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes_pendientes as $solicitud): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($solicitud['nombre_institucion']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['ruc']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['email']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['fecha_solicitud']); ?></td>
                            <td><a href="../../<?php echo htmlspecialchars($solicitud['documento_path']); ?>" target="_blank">Ver PDF</a></td>
                            <td class="actions">
                                <form action="" method="POST">
                                    <input type="hidden" name="solicitud_id" value="<?php echo $solicitud['id']; ?>">
                                    <input type="hidden" name="action" value="aprobar">
                                    <button type="submit" class="btn-approve" onclick="return confirm('¿Estás seguro de que quieres aprobar esta solicitud?');">Aprobar</button>
                                </form>
                                <button class="btn-reject" onclick="openRejectModal(<?php echo $solicitud['id']; ?>)">Rechazar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($solicitudes_pendientes)): ?>
                        <tr><td colspan="6">No hay solicitudes pendientes.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pestaña de Aprobadas -->
        <div id="aprobadas" class="tab-content">
            <h2>Solicitudes Aprobadas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Institución</th>
                        <th>RUC</th>
                        <th>Email</th>
                        <th>Fecha Solicitud</th>
                        <th>Fecha Revisión</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes_aprobadas as $solicitud): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($solicitud['nombre_institucion']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['ruc']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['email']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['fecha_solicitud']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['fecha_revision'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($solicitudes_aprobadas)): ?>
                        <tr><td colspan="5">No hay solicitudes aprobadas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pestaña de Rechazadas -->
        <div id="rechazadas" class="tab-content">
            <h2>Solicitudes Rechazadas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Institución</th>
                        <th>RUC</th>
                        <th>Email</th>
                        <th>Fecha Solicitud</th>
                        <th>Motivo del Rechazo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($solicitudes_rechazadas as $solicitud): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($solicitud['nombre_institucion']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['ruc']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['email']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['fecha_solicitud']); ?></td>
                            <td><?php echo htmlspecialchars($solicitud['motivo_rechazo'] ?? 'No especificado'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($solicitudes_rechazadas)): ?>
                        <tr><td colspan="5">No hay solicitudes rechazadas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

    <!-- El Modal para Rechazar -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close-button" onclick="closeRejectModal()">&times;</span>
            <h2>Motivo del Rechazo</h2>
            <form action="" method="POST">
                <input type="hidden" name="solicitud_id" id="modal_solicitud_id">
                <input type="hidden" name="action" value="rechazar">
                <textarea name="motivo_rechazo" rows="4" style="width: 100%;" placeholder="Explica por qué se rechaza la solicitud..." required></textarea>
                <br><br>
                <button type="submit" class="btn-reject">Confirmar Rechazo</button>
            </form>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tab-link");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        function openRejectModal(solicitudId) {
            document.getElementById('modal_solicitud_id').value = solicitudId;
            document.getElementById('rejectModal').style.display = 'block';
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }
    </script>
</body>
</html>
