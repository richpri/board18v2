#!/usr/bin/env python

# Copyright (c) 2014 Richard E. Price under the The MIT License.
# A copy of this license can be found in the LICENSE.text file
# or at http://opensource.org/licenses/MIT 
import os
from gimpfu import *

def new_token_sheet(img, layer, rowCount, columnCount):
    ''' Create a new token sheet.
    
    Parameters:
    img : image The current image (unused).
    layer : layer The layer of the image that is selected (unused).
    rowCount : [int] The number of rows in this token sheet.
    columnCount: [int] The number of columns in this token sheet.
    '''
    # Calculate the height of the token sheet.
    newHeight = 10 + (rowCount * 40)
    # Calculate the width of the token sheet.
    newWidth = 10 + (columnCount * 40)

    theImage = pdb.gimp_image_new(newWidth, newHeight,  0)
    theLayer = pdb.gimp_layer_new(theImage, newWidth, newHeight, 0, "theLayer", 100, 0)
    pdb.gimp_layer_add_alpha(theLayer)
    pdb.gimp_drawable_fill(theLayer, 3)
    pdb.gimp_image_add_layer(theImage, theLayer, 0)
    pdb.gimp_display_new(theImage)
    return 0
    
register(
    "new_token_sheet",
    "Create a new token sheet.",
    "Create a new token sheet.",
    "Rich Price",
    "Rich Price",
    "2014",
    "<Image>/Filters/BD18/New-Token-Sheet",
    "",
    [(PF_SPINNER, "rowCount", "Row Count", 1, (1,20,1)),
     (PF_SPINNER, "columnCount", "Column Count", 1, (1,2,1)), 
    ],
    [],
    new_token_sheet)

main()
