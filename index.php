<?php

$title = "IP Checker Service"; // website's title
$servers = [
    '1' => [
        'ip' => 'ip',
        'port' => 'port',
        'info' => 'web name'
    ]
];


if (isset($_GET['host'])) {
    $host = $_GET['host'];

    if (isset($servers[$host])) {
        header('Content-Type: application/json');

        $return = array(
            'status' => doSocket($servers[$host])
        );

        echo json_encode($return);
        exit;
    } else {
        header("HTTP/1.1 404 Not Found");
    }
}

$names = array();
foreach ($servers as $name => $info) {
    $names[$name] = md5($name);
}


?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootswatch/2.3.2/cosmo/bootstrap.min.css">
        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.min.css">
        <style type="text/css">
                /* Custom Styles */
                .i-success{
                    color:green;
                }
                .i-error{
                    color:red;
                }
        </style>
    </head>
    <body style="background-color: black; color:white;">

        <div class="container">
            <h1><?php echo $title; ?></h1>
            <table class="table">
                <thead>
                    <tr>
                        <th></th>
                        <th>#</th>
                        <th>Host</th>
                        <th>Info</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($servers as $name => $server): ?>

                        <tr id="<?php echo md5($name); ?>">
                            <td><i class="icon-spinner icon-spin icon-large"></i></td>
                            <td class="name"><?php echo $name; ?></td>
                            <td><?php echo $server['ip']; ?></td>
                            <td><?php echo $server['info']; ?></td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>
        </div>

        <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery.min.js"></script>
        <script type="text/javascript">

            function test(host, hash) {
                // Fork it
                var request;

                // fire off the request to /form.php
                request = $.ajax({
                    url: "<?php echo basename(__FILE__); ?>",
                    type: "get",
                    data: {
                        host: host
                    },
                    beforeSend: function () {
                        $('#' + hash).children().children().css({'visibility': 'visible'});
                    }
                });

                // callback handler that will be called on success
                request.done(function (response, textStatus, jqXHR) {
                    var status = response.status;
                    var statusClass;
                    if (status) {
                        statusClass = 'i-success';
                    } else {
                        statusClass = 'i-error';
                    }

                    $('#' + hash).removeClass('i-success i-error').addClass(statusClass);
                });

                // callback handler that will be called on failure
                request.fail(function (jqXHR, textStatus, errorThrown) {
                    // log the error to the console
                    console.error(
                        "The following error occured: " +
                            textStatus, errorThrown
                    );
                });


                request.always(function () {
                    $('#' + hash).children().children().css({'visibility': 'hidden'});
                })

            }

            $(document).ready(function () {

                var servers = <?php echo json_encode($names); ?>;
                var server, hash;

                for (var key in servers) {
                    server = key;
                    hash = servers[key];

                    test(server, hash);
                    (function loop(server, hash) {
                        setTimeout(function () {
                            test(server, hash);

                            loop(server, hash);
                        }, 6000);
                    })(server, hash);
                }

            });
        </script>

    </body>
</html>
<?php
/* Misc at the bottom */
function test($server) {
    $socket = fsockopen('ssl://'.$server['ip'], $server['port'], $errorNo, $errorStr, 3);
die($socket);
    if ($errorNo == 0) {
        return $socket;
    } else {
        return $socket;
    }
}

function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

function doSocket($address){
    // print_r($address);
    // The TCP port to test
    $testport = $address['port'];
    // The length of time in seconds to allow host to respond
    $waitTimeout = 5;
    $addresses[]=$address['ip'];
    $socks = array();
    foreach ($addresses as $address) {
    if (!$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
      // echo "Could not create socket for $address---\n";
      continue;
    } 
    // else echo "Created socket for $address---\n";
    socket_set_nonblock($sock);
    // Suppress the error here, it will always throw a warning because the
    // socket is in non blocking mode
    @socket_connect($sock, $address, $testport);
    $socks[$address] = $sock;
    }

    // Sleep to allow sockets to respond
    // In theory you could just pass $waitTimeout to socket_select() but this can
    // be buggy with non blocking sockets
    sleep($waitTimeout);

    // Check the sockets that have connected
    $w = $socks;
    $r = $e = NULL;
    $count = socket_select($r, $w, $e, 0);
    // echo "$count sockets connected successfully---\n";

    // Loop connected sockets and retrieve the addresses that connected
    foreach ($w as $sock) {
    $address = array_search($sock, $socks);
    // echo "$address connected successfully---\n";
    @socket_close($sock);
    return true;
    }

    return false;
}
?>