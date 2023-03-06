#!/usr/bin/env python

# Copyright (c) 2014 Richard E. Price under the The MIT License.
# A copy of this license can be found in the LICENSE.text file
# or at http://opensource.org/licenses/MIT 
import os
from gimpfu import *

def new_tile_P_sheet(img, layer, columnCount):
    ''' Create a new P type tile sheet.
    
    Parameters:
    img : image The current image (unused).
    layer : layer The layer of the image that is selected (unused).
    columnCount : [int] The number of columns in this tile sheet.
    '''
    # Calculate the width of the tile sheet.
    newWidth = 20 + (columnCount * 120)
    img = pdb.gimp_image_new(newWidth, 800, 0)
    layer0 = pdb.gimp_layer_new(img, newWidth, 800, 0, "layer0", 100, 0)
    pdb.gimp_layer_add_alpha(layer0)
    pdb.gimp_drawable_fill(layer0, 3)
    pdb.gimp_image_add_layer(img, layer0, 0)
    pdb.gimp_display_new(img)
    return 0
    
register(
    "new_tile_P_sheet",
    "Create a new type P tile sheet",
    "Create a new type P tile sheet",
    "Rich Price",
    "Rich Price",
    "2014",
    "<Image>/Filters/BD18/New-Tile-P-Sheet",
    "",
    [(PF_SPINNER, "columnCount", "Column Count", 1, (1,30,1))],
    [],
    new_tile_P_sheet)

main()
