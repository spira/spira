<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Spira - API Documentation</title>

    </head>
    <body>

        <script src="https://api.apiary.io/seeds/embed.js"></script>
        <script>

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {

                    new Apiary.Embed({
                        apiBlueprint: xhr.responseText
                    });

                }
            };
            xhr.open('GET', '{{ $apibUrl }}', true);
            xhr.send();


        </script>
    </body>
</html>