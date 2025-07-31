<?php
    session_start();
    
    session_destroy();
    
    header(header: 'Location: ./loginUsuario.php');
    exit;