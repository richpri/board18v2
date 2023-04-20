#!/usr/bin/env python

# Copyright (c) 2014 Richard E. Price under the The MIT License.
# A copy of this license can be found in the LICENSE.text file
# or at http://opensource.org/licenses/MIT 
import os
from gimpfu import *

def add_tile_F_to_sheet(img, layer, tileName, columnNumb, rowCount):
    ''' Create a column of tiles on a type F tile sheet.
    
    Parameters:
    img : image The current image.
    layer : layer The layer of the image that is selected (unused).
    tileName : [string] The file name of the input tile image.
    columnNumb : [int] The tile sheet column to create.
    rowCount : [int] The number of rows in this column.
    '''
    inLayer = pdb.gimp_image_get_active_layer(img)
    # Open the tile image file
    theTile = None
    if(tileName.lower().endswith(('.png'))):
        theTile = pdb.file_png_load(tileName, tileName)
    else:
        pdb.gimp.message("Input file must be png.")
        return 1
    if (theTile == None):
        pdb.gimp.message("Input file not found: " + tileName)
        return 1
    # Copy the tile into the edit buffer.
    tileLayer = theTile.layers[0]
    if (pdb.gimp_edit_copy(tileLayer) == FALSE):
        pdb.gimp.message("Copy failed on input tile.")
        return 1
    # Calculate desired column position for tile.
    newX = 20 + ((columnNumb - 1) * 130)
    for i in range(int(rowCount)):  
        # Paste the tile into the tile sheet.
        theSel = pdb.gimp_edit_paste(inLayer, 0)
        # Find current position of tile.
        (offsetX, offsetY) = pdb.gimp_drawable_offsets(theSel)
        # Calculate desired row position for tile.
        newY = 20 + (i * 120)
        # Reposition the tile on the tile sheet.
        pdb.gimp_layer_translate(theSel, newX-offsetX, newY-offsetY)
        # Rotate the tile to the correct orientation.
        rot = (3.14159 / 3) * i
        pdb.gimp_drawable_transform_rotate_default(theSel, rot,
            1, 0, 0, 1, 0)
        pdb.gimp_floating_sel_anchor(theSel)
    return 0
    
register(
    "add_tile_F_to_sheet",
    "Create a column of type F tiles on a tile sheet",
    "Create a column of type F tiles on a tile sheet",
    "Rich Price",
    "Rich Price",
    "2014",
    "<Image>/Filters/BD18/Add-Tile-F-to-Sheet",
    "*",
    [
        (PF_FILE, "tileName", "Tile to Add", ""),
        (PF_SPINNER, "columnNumb", "Column", 1, (1,30,1)),
        (PF_SPINNER, "rowCount", "Row Count", 6, (1,6,1))
    ],
    [],
    add_tile_F_to_sheet)

main()
