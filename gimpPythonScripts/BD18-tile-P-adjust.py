#!/usr/bin/env python

# Copyright (c) 2020 Richard E. Price under the The MIT License.
# A copy of this license can be found in the LICENSE.text file
# or at http://opensource.org/licenses/MIT 

from gimpfu import *

def tile_P_adjust(inImg, inLayer) :
    ''' Adjust the scale of a type P tile image 
        and make background transparent.
    
    Parameters:
    inImg   : The current image.
    inLayer : The layer of the image that is selected.
    '''
    w = gimp.pdb.gimp_image_width(inImg) + 100
    h = gimp.pdb.gimp_image_height(inImg) + 100
    pdb.gimp_image_grid_set_background_color(inImg, (255, 255, 255))
    pdb.gimp_image_resize(inImg, w, h, 50, 50)
    bigLayer = pdb.gimp_image_flatten(inImg)
    pdb.gimp_image_select_contiguous_color(inImg, 0, bigLayer, 5, 5)
    pdb.gimp_layer_add_alpha(bigLayer)
    pdb.plug_in_colortoalpha(inImg, bigLayer, (255, 255, 255))
    pdb.gimp_selection_none(inImg)
    pdb.plug_in_autocrop(inImg, bigLayer)
    pdb.gimp_image_scale(inImg, 100, 116)
    
register(
    "tile_P_adjust",
    "Adjust the scale of a type P tile image and make background transparent",
    "Adjust the scale of a type P tile image and make background transparent",
    "Rich Price",
    "Rich Price",
    "2014",
    "<Image>/Filters/BD18/Tile-P-adjust",
    "*",
    [],
    [],
    tile_P_adjust)

main()
