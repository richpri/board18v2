#!/usr/bin/env python

# Copyright (c) 2014 Richard E. Price under the The MIT License.
# A copy of this license can be found in the LICENSE.text file
# or at http://opensource.org/licenses/MIT 
import os
from gimpfu import *

def batch_tile_adjust(img, layer, inputFolder, outputFolder):
    ''' Adjust the scale and orientation of all tile images 
    in the input folder.
    
    Parameters:
    img : image The current image (unused).
    layer : layer The layer of the image that is selected (unused).
    inputFolder : [string] The folder of input tile images.
    outputFolder : [string] The folder for the output tile images.
    '''
    # Iterate the folder
    for file in os.listdir(inputFolder):
        try:
            # Build the full file paths.
            inputPath = inputFolder + "/" + file
            outputPath = outputFolder + "/" + file
        
            # Open the file if is a PNG image.
            image = None
            if(file.lower().endswith(('.png'))):
                image = pdb.file_png_load(inputPath, inputPath)
                
            # Verify if the file is an image.
            if(image != None):
                # Adjust the scale and orientation of the image.
                if(len(image.layers) > 0):
                    layer = image.layers[0]
                    pdb.python_fu_tile_P_adjust(image, layer)
                    
                    # Save the image.
                    pdb.file_png_save(image, image.layers[0], outputPath, outputPath, 0, 9, 0, 0, 0, 0, 0)
                    
        except Exception as err:
            gimp.message("Unexpected error: " + str(err))

register(
    "batch_tile_adjust",
    "Batch tile adjust",
    "Adjust the scale and orientation of all tiles in the input folder.",
    "Rich Price",
    "Open source MIT License",
    "2014",
    "<Image>/Filters/BD18/Batch-Tile-Adjust",
    "",
    [
        (PF_DIRNAME, "inputFolder", "Input directory", ""),
        (PF_DIRNAME, "outputFolder", "Output directory", "")
    ],
    [],
    batch_tile_adjust)

main()
