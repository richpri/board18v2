#!/usr/bin/env python

# Copyright (c) 2015 Richard E. Price under the The MIT License.
# A copy of this license can be found in the LICENSE.text file
# or at http://opensource.org/licenses/MIT 
import os
from gimpfu import *

def add_token_to_sheet(img, layer, tokenName, rowNumb, columnNumb, areaWidth, areaHeight):
    ''' Paste a nonstandard sized token onto the token sheet.
    
    Parameters:
    img : image The current token sheet image.
    layer : layer The layer of the image that is selected (unused).
    tokenName : [string] The file name of the input token image.
    rowNumb : [int] The token sheet row to paste into.
    columnNumb : [int] The token sheet column to paste into.
    areaWidth : the width of the token placement area.
    areaHeight : the height of the token placement area.
    '''
    inLayer = pdb.gimp_image_get_active_layer(img)
    # Open the token image file
    theToken = None
    if(tokenName.lower().endswith(('.png'))):
        theToken = pdb.file_png_load(tokenName, tokenName)
    else:
        pdb.gimp.message("Input file must be png.")
        return 1
    if (theToken == None):
        pdb.gimp.message("Input file not found: " + tokenName)
        return 1
    # Copy the token into the edit buffer.
    tokenLayer = theToken.layers[0]
    if (pdb.gimp_edit_copy(tokenLayer) == FALSE):
        pdb.gimp.message("Copy failed on input token.")
        return 1
    # Find the dimensions of the token.
    tokenWidth = pdb.gimp_drawable_width(tokenLayer)
    tokenHeight = pdb.gimp_drawable_height(tokenLayer)
    # Calculate desired column position for token.
    adjustX = (areaWidth - tokenWidth) / 2
    adjustY = (areaHeight - tokenHeight) / 2
    newX = 10 + ((columnNumb - 1) * (areaWidth + 10)) + adjustX
    newY = 10 + ((rowNumb - 1) * (areaHeight + 10)) + adjustY
    # Paste the token into the token sheet.
    theSel = pdb.gimp_edit_paste(inLayer, 0)
    # Find current position of token.
    (offsetX, offsetY) = pdb.gimp_drawable_offsets(theSel)
    # Reposition the token on the token sheet.
    pdb.gimp_layer_translate(theSel, newX-offsetX, newY-offsetY)
    pdb.gimp_floating_sel_anchor(theSel)
    return 0
    
register(
    "add_tokenX_to_sheet",
    "Paste a nonstandard sized token onto the token sheet.",
    "Paste a nonstandard sized token onto the token sheet.",
    "Rich Price",
    "Rich Price",
    "2014",
    "<Image>/Filters/BD18/Add-TokenX-to-Sheet",
    "*",
    [
        (PF_FILE, "tokenName", "Token to Add", ""),
        (PF_SPINNER, "rowNumb", "Row", 1, (1,20,1)),
        (PF_SPINNER, "columnNumb", "Column", 1, (1,2,1)),
        (PF_INT, "areaWidth", "Width", 30),
        (PF_INT, "areaHeight", "Height", 30),
    ],
    [],
    add_token_to_sheet)

main()
