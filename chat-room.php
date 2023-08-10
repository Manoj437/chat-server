<?php
// Chat room server settings
$host = "127.0.0.1"; // Change this to your server's IP
$port = 12345;      // Choose an available port

// Create a socket
$serverSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($serverSocket, $host, $port);
socket_listen($serverSocket);

// Create an array to store connected clients
$clients = array($serverSocket);

echo "Chat room server started on $host:$port\n";

while (true) {
    $read = $clients;
    $write = $except = null;

    // Set up a select call
    socket_select($read, $write, $except, null);

    foreach ($read as $readSocket) {
        if ($readSocket === $serverSocket) {
            // New client is trying to connect
            $newClient = socket_accept($serverSocket);
            $clients[] = $newClient;

            // Send a welcome message to the new client
            socket_write($newClient, "Welcome to the chat room!\n");

            echo "New client connected.\n";
        } else {
            // Handle data from a client
            $data = socket_read($readSocket, 1024);

            if ($data === false) {
                // Client disconnected
                $index = array_search($readSocket, $clients);
                socket_close($readSocket);
                unset($clients[$index]);

                echo "Client disconnected.\n";
            } else {
                // Broadcast the received message to all clients
                foreach ($clients as $client) {
                    if ($client !== $serverSocket && $client !== $readSocket) {
                        socket_write($client, "Client " . array_search($readSocket, $clients) . ": $data");
                    }
                }
            }
        }
    }
}

// Close the server socket
socket_close($serverSocket);
?>
