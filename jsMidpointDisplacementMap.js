function MidpointDisplacementMapGenerator(setSize, displacement) {
    var 
        size = setSize,
        map = [],

        // the value the displacement changes with each recursion
        // each recursion cuts the width in half, so a sensible value
        // may be 2. a value of 1. makes it very noisy, 
        // but all values > 0 are possible
        d = displacement;
    
    // set the center point and the top, left, right and lower point 
    // and recurse over the 4 new squares
    var divide = function (x1, y1, x2, y2, dh) {
        var dx = x2-x1,
            dy = y2-y1,
            cx = x1+dx/2,
            cy = y1+dy/2,
            d2 = dh/2;
        
        if (dx > 1) {
            // generate center pt
            map[cy][cx] = (map[y1][x1] + map[y2][x2] + map[y1][x2] + map[y2][x1])/4 + Math.random() * dh - d2;
        
            // generate top, bottom, left and right pts
            if (map[y1][cx] === undefined) map[y1][cx] = (map[y1][x1] + map[y1][x2])/2 + Math.random() * dh - d2;
            if (map[y2][cx] === undefined) map[y2][cx] = (map[y2][x1] + map[y2][x2])/2 + Math.random() * dh - d2;
            if (map[cy][x1] === undefined) map[cy][x1] = (map[y1][x1] + map[y2][x1])/2 + Math.random() * dh - d2;
            if (map[cy][x2] === undefined) map[cy][x2] = (map[y1][x2] + map[y2][x2])/2 + Math.random() * dh - d2;
            
            var nh = dh/d;

            // recurse!
            divide(x1, y1, cx, cy, nh);
            divide(cx, y1, x2, cy, nh);
            divide(x1, cy, cx, y2, nh);
            divide(cx, cy, x2, y2, nh);
        }
    }
    
    // prepare
    this.generate = function () {
        // init array
        for (var i=0; i<size; i++)
            map[i] = [];
        
        // preset the corner values
        map[0][0] = Math.random();
        map[0][size-1] = Math.random();
        map[size-1][0] = Math.random();
        map[size-1][size-1] = Math.random();
        
        // start
        divide(0, 0, size-1, size-1, 1);
        
        return map;
    }        
}