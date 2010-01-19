<!DOCTYPE HTML>
<html>
<head>
<title>JavaScript midpoint displacement map</title>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.min.js"></script>
<script type="text/javascript" src="jsMidpointDisplacementMap.js"></script>
<script type="text/javascript">

    function getTime() {
        return (new Date()).getTime();
    }

    // adjusts values to between 0 and mdiff (e.g. 0 and 255 for drawing)
    function adjust(map, mdiff) {
        var s = false, b = false, d;
        for (var y=0; y<map.length; y++)
            for (var x=0; x<map[y].length; x++) {
                if (map[y][x] < s || s === false)
                    s = map[y][x];

                if (map[y][x] > b || b === false)
                    b = map[y][x];
            }
            
        d = b-s;
            
        for (var y=0; y<map.length; y++)
            for (var x=0; x<map[y].length; x++) {
                map[y][x] += -s;
                map[y][x] = Math.floor(map[y][x] / d * mdiff);
            }
            
        return map;
    }
    
    // draws map grayscale
    function draw(canvas, map) {
        var ctx = canvas.getContext('2d');
        
        for (var y=0; y<map.length; y++)
            for (var x=0; x<map[y].length; x++) {
                var c = map[y][x];
                ctx.fillStyle = "rgb(" + [c,c,c].join(',') + ")";  
                ctx.fillRect (x, y, x+1, y+1);              
            }
    }
    
    // draws map colored
    function drawColoredMap(canvas, map) {
        var ctx  = canvas.getContext('2d');
        
        for (var y=0; y<map.length; y++)
            for (var x=0; x<map[y].length; x++) {
                var 
                    c = map[y][x];
                
                if (c < 96) {
                    ctx.fillStyle = "rgb(" + [0,0,c+64].join(',') + ")";
                    
                } else if (c >= 96 && c < 192) {
                    ctx.fillStyle = "rgb(" + [0,c,0].join(',') + ")";
                    
                } else if (c >= 192) {
                    ctx.fillStyle = "rgb(" + [c,c,c].join(',') + ")";
                }
                    
                ctx.fillRect (x, y, x+1, y+1);              
            }

    }

    // don't draw with the context image functions
    function drawColoredMapImgd(canvas, cmap, water, forest, shadows, blur) {
        var map = blur ? blurMap(cmap) : cmap,
            h = map.length,
            w = map[0].length,
            ctx = canvas.getContext('2d'),
            imgd = ctx.getImageData(0, 0, w, h),
            pixm = imgd.data,
            c, rgb, incl;

        for (var y=0; y<h; y++)
            for (var x=0; x<w; x++) {
                // for the last line, incl = 0
                incl = y<h-1 ? (map[y][x] - map[y+1][x])*3 : 0;
                c = map[y][x];
                wc  = shadows ? Math.floor(Math.max(Math.min(map[y][x]-incl, 255), 0)) : c;

                rgb = [0, 0, 0];
                
                if (c < water) {
                    rgb = [0, 0, c+64];

                } else if (c >= water && c < forest) {
                    rgb = [0, wc, 0];
                    
                } else if (c >= forest) {
                    rgb = [c, wc, c];
                }
                
                var koo = y*w*4 + (x*4);
                pixm[koo + 0] = rgb[0];
                pixm[koo + 1] = rgb[1];
                pixm[koo + 2] = rgb[2];
                pixm[koo + 3] = 255;
            }

        ctx.putImageData(imgd, 0, 0);
    }

    // blur map
    function blurMap(map) {
        var nm = map.clone();

        for (var y=1; y<map.length-1; y++)
            for (var x=1; x<map[y].length-1; x++)
                nm[y][x] = 
                    (map[y-1][x-1] + map[y-1][x] + map[y-1][x+1] + map[y][x-1] + map[y][x] + map[y][x+1] + map[y+1][x-1] + map[y+1][x] + map[y+1][x+1]) / 9;

        return nm;
    }


    // setup and UI
    $(document).ready(function () {
        Object.prototype.clone = function() { return eval(uneval(this)); }

        var hmap = [];

        // create map size selector
        for (var i=5; i<12; i++)
            $('#size').append('<option ' + (i==8 ? 'selected="selected"' : '') + '>' + (Math.pow(2, i)+1) + '</option>');

        var draw = function (map) {
            drawColoredMapImgd($('#canv').get(0), map, parseInt($('#water').val()), parseInt($('#forest').val()), $('#shadows:checked').val(), parseInt($('#blur:checked').val()));
        }

        var gen = function () {
            // get selected size
            var size = parseInt($('#size OPTION:selected').val());

            // set width and height of the canvas
            $('#canv').attr('width', size);
            $('#canv').attr('height', size);
        
            // start generating
            var tStartGen = getTime();
            var generator = new MidpointDisplacementMapGenerator(size, parseFloat($('#variability').val()));

            // generate the map
            var map = generator.generate();
            var genTime = getTime() - tStartGen;
            
            tStart = getTime();
            // adjust values to between 0 and 255
            var map = adjust(map, 255);
            var adjTime = getTime() - tStart;

            tStart = getTime();
            draw(map);
            // draw the map to canvas
            var drawTime = getTime() - tStart;

            $('#time').html('time to generate: ' + genTime + 'ms, time to adjust: ' + adjTime + 'ms, time to draw: ' + drawTime + 'ms, total: ' + (getTime()-tStartGen) + 'ms');

            return map;
        }

        $('#start').click(function () {
            hmap = gen();
        });

        $('#water, #forest, #shadows, #blur').change(function () {
            draw(hmap);
        });

        var adjInpVal = function (elem, op) {
            elem.val(Math.min(Math.max(parseInt(elem.val())+op, 0), 256));
            draw(hmap);
        }

        // +-btns
        $('#wp').click(function () { adjInpVal($('#water'), +1); });
        $('#wm').click(function () { adjInpVal($('#water'), -1); });
        $('#fp').click(function () { adjInpVal($('#forest'), +1); });
        $('#fm').click(function () { adjInpVal($('#forest'), -1); });

        // create initial map
        hmap = gen();
    });
</script>
<style type="text/css">
BODY {
    font-size: 10pt;
    font-family: Helvetica, Verdana, Arial;
}

canvas {
    border: 1px solid black;
}
</style>
</head>
    
<body>
<h1>midpoint displacement height map</h1>
<p>Explanation: <a href="http://www.gameprogrammer.com/fractal.html#diamond">www.gameprogrammer.com</a>. Uses html5 canvas, so you need FF, chrome, safari or opera. No IE.</p>

<canvas id="canv" width="0" height="0"></canvas>

<fieldset><legend>adjust coloring</legend>
Water: <input id="water" type="text" value="96"> <input id="wp" type="button" value="+"><input id="wm" type="button" value="-">, 
Forest: <input id="forest" type="text" value="192"> <input id="fp" type="button" value="+"><input id="fm" type="button" value="-">, 
<abbr title="Sun in the north">Shadows</abbr>: <input id="shadows" type="checkbox" value="1">,
<abbr title="makes drawing a lot slower">Blur</abbr>: <input id="blur" type="checkbox" value="1"></fieldset>

<fieldset><legend>generate</legend>
Size: <select id="size"></select>, Variability (best between 1..2): <input id="variability" type="text" value="1.4"> <input id="start" type="button" value="go!"><br/>
Time: <span id="time"></span>
</fieldset>


<p>Questions? Suggestions? Spam? Try <a href="mailto:sirmonko@tapirpirates.net">sirmonko@tapirpirates.net</a>, last updated <?php echo date('Y-m-d', filectime(__FILE__)); ?></p>
</body>
</html>