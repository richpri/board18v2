#!/usr/bin/env python

# Copyright (c) 2014 Richard E. Price under the The MIT License.
# A copy of this license can be found in the LICENSE.text file
# or at http://opensource.org/licenses/MIT 
import os
from gimpfu import *

def add_token_to_sheet(img, layer, tokenName, rowNumb, columnNumb):
    ''' Paste a token onto the token sheet.
    
    Parameters:
    img : image The current token sheet image.
    layer : layer The layer of the image that is selected (unused).
    tokenName : [string] The file name of the input token image.
    rowNumb : [int] The token sheet row to paste into.
    columnNumb : [int] The token sheet column to paste into.
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
    # Calculate desired column position for token.
    newX = 10 + ((columnNumb - 1) * 40)
    newY = 10 + ((rowNumb - 1) * 40)
    # Paste the token into the token sheet.
    theSel = pdb.gimp_edit_paste(inLayer, 0)
    # Find current position of token.
    (offsetX, offsetY) = pdb.gimp_drawable_offsets(theSel)
    # Reposition the token on the token sheet.
    pdb.gimp_layer_translate(theSel, newX-offsetX, newY-offsetY)
    pdb.gimp_floating_sel_anchor(theSel)
    return 0
    
register(
    "add_token_to_sheet",
    "Paste a token onto the token sheet.",
    "Paste a token onto the token sheet.",
    "Rich Price",
    "Rich Price",
    "2014",
    "<Image>/Filters/BD18/Add-Token-to-Sheet",
    "*",
    [
        (PF_FILE, "tokenName", "Token to Add", ""),
        (PF_SPINNER, "rowNumb", "Row", 1, (1,20,1)),
        (PF_SPINNER, "columnNumb", "Column", 1, (1,2,1)),
    ],
    [],
    add_token_to_sheet)

main()