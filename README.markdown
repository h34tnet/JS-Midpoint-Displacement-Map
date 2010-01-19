# JS Midpoint Displacement Heightmap for Prodcedural World Generation

Midpoint displacement and the used diamond square algorithm are described here:
http://www.gameprogrammer.com/fractal.html#diamond

It's actually quite simple.

## Q

* *What does the "variability" parameter mean?*  
  It controls the fragmentation - do you want several small islands or one big continent? Its achieved by changing the height adjusting divisor when recursing. So, if one square is divided in 4 subsquares, the maximum height that is randomly added may be some value >0.


## Obviously problematic
* Blurring ignores the left and right columns and the first and alst row (the old problem). I could do it right (http://www.jhlabs.com/ip/blurring.html), but blurring is just cosmetic anyways.
* Also, blurring is applied every time before rendering, even if it doesn't have to -> slooow!