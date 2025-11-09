<?php
// Start session
session_start();

// Hapus semua data session
session_unset();

// Hancurkan session
session_destroy();

// Respon sukses
echo json_encode(['message' => 'Logout successful']);
