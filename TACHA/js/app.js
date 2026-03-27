(function () {
    function drawPseudoQR(canvas, token) {
        if (!canvas || !token) {
            return;
        }

        var ctx = canvas.getContext('2d');
        var size = 29;
        var cell = Math.floor(canvas.width / size);
        var seed = 0;

        for (var i = 0; i < token.length; i++) {
            seed = (seed * 31 + token.charCodeAt(i)) >>> 0;
        }

        function rand() {
            seed = (1664525 * seed + 1013904223) >>> 0;
            return seed / 4294967296;
        }

        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        function finder(x, y) {
            ctx.fillStyle = '#0b1f3a';
            ctx.fillRect(x * cell, y * cell, 7 * cell, 7 * cell);
            ctx.fillStyle = '#ffffff';
            ctx.fillRect((x + 1) * cell, (y + 1) * cell, 5 * cell, 5 * cell);
            ctx.fillStyle = '#0b1f3a';
            ctx.fillRect((x + 2) * cell, (y + 2) * cell, 3 * cell, 3 * cell);
        }

        finder(1, 1);
        finder(size - 8, 1);
        finder(1, size - 8);

        ctx.fillStyle = '#0b1f3a';
        for (var y = 0; y < size; y++) {
            for (var x = 0; x < size; x++) {
                var inFinder =
                    (x >= 1 && x <= 7 && y >= 1 && y <= 7) ||
                    (x >= size - 8 && x <= size - 2 && y >= 1 && y <= 7) ||
                    (x >= 1 && x <= 7 && y >= size - 8 && y <= size - 2);

                if (inFinder) {
                    continue;
                }

                if (rand() > 0.5) {
                    ctx.fillRect(x * cell, y * cell, cell, cell);
                }
            }
        }
    }

    document.querySelectorAll('[data-ticket-token]').forEach(function (el) {
        var token = el.getAttribute('data-ticket-token');
        var canvas = document.getElementById(el.getAttribute('data-qr-canvas'));
        drawPseudoQR(canvas, token);
    });
})();
