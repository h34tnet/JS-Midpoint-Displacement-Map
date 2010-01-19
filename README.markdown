# JS Midpoint Displacement Heightmap for Prodcedural World Generation

Midpoint displacement and the used diamond square algorithm are described here:  
http://www.gameprogrammer.com/fractal.html#diamond

It's actually quite simple.

## What?

* *What does the "variability" parameter mean?*  
  It controls the fragmentation - do you want several small islands or one big continent?  
  Rule of thumb: the bigger, the smoother.  
  Imho values between ~1.4 and 2 look good.


## Obviously problematic
* Blurring ignores the left and right columns and the first and last row (the old problem). I could do it right (http://www.jhlabs.com/ip/blurring.html), but blurring is just cosmetic anyways (also, you almost can't see the unblurred rows).
* Also, blurring is applied every time before rendering, even if it doesn't have to -> slooow!


# Usage
        
    var 
        // size MUST be 2^n+1 (e.g. 3, 5, 9, 17, 33, 65, 129, 257, 513, ...)
        size        = 513,
      
        // the bigger, the smoother
        variability = 1.5,

        generator = new MidpointDisplacementMapGenerator(size, variability),

        // now we get a 2-dimensional, square array[0..size-1, 0..size-1] 
        // filled with floats between 0 and 255. yay!
        map       = generator.generate(255);

    // now do something with it
    console.log(map);

