#!/usr/bin/env python

# Copyright (c) 2014 Richard E. Price under the The MIT License.
# A copy of this license can be found in the LICENSE.text file
# or at http://opensource.org/licenses/MIT 

from gimpfu import *

def tile_adjust(inImg, inLayer) :
    ''' Adjust the scale and orientation of a tile image.
    
    Parameters:
    inImg   : The current image.
    inLayer : The layer of the image that is selected.
    '''
    w = gimp.pdb.gimp_image_width(inImg) + 100
    h = gimp.pdb.gimp_image_height(inImg) + 100
    rot = 3.14159 / 6
    pdb.gimp_image_resize(inImg, w, h, 50, 50)
    bigLayer = pdb.gimp_image_flatten(inImg)
    pdb.gimp_drawable_transform_rotate_default(
        bigLayer,
        rot,
        1,
        0,
        0,
        1,
        0
    )
    rotLayer = pdb.gimp_image_flatten(inImg)
    pdb.plug_in_autocrop(inImg, rotLayer)
    pdb.gimp_image_scale(inImg, 100, 116)
    pdb.gimp_fuzzy_select(rotLayer, 5, 5, 20, 0, 1, 0, 0, 0)
    pdb.gimp_layer_add_alpha(rotLayer)
    pdb.plug_in_colortoalpha(inImg, rotLayer, (255, 255, 255))
    pdb.gimp_selection_none(inImg)
    
register(
    "tile_adjust",
    "Adjust the scale and orientation of a tile image",
    "First rotate image by 30 degrees then scale it to 100x116 pix",
    "Rich Price",
    "Open source MIT License",
    "2014",
    "<Image>/Filters/BD18/Tile-Adjust",
    "*",
    [],
    [],
    tile_adjust)

main()
