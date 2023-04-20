#!/usr/bin/env python
#
# -------------------------------------------------------------------------------------
#
# Copyright (c) 2013, Jose F. Maldonado
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without modification, 
# are permitted provided that the following conditions are met:
#
#    - Redistributions of source code must retain the above copyright notice, this 
#    list of conditions and the following disclaimer.
#    - Redistributions in binary form must reproduce the above copyright notice, 
#    this list of conditions and the following disclaimer in the documentation and/or 
#    other materials provided with the distribution.
#    - Neither the name of the author nor the names of its contributors may be used 
#    to endorse or promote products derived from this software without specific prior 
#    written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY 
# EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES 
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT 
# SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, 
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED
# TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR 
# BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
# CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN 
# ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH 
# DAMAGE.
#
# Modified by Rich Price - July 2015.
#
import os
from gimpfu import *

def batch_token_invert(img, layer, inputFolder, outputFolder):
    ''' Inverts the colors of the PNG token images in a folder.
    
    Parameters:
    img : image The current image (unused).
    layer : layer The layer of the image that is selected (unused).
    inputFolder : string The folder of the images that must be inverted.
    outputFolder : string The folder in which save the inverted images.
    '''
    # Iterate the folder
    for file in os.listdir(inputFolder):
        try:
            # Build the full file paths.
            inputPath = inputFolder + "\\" + file
            ofile = file[:-4] + "flip.png"
            outputPath = outputFolder + "\\" + ofile
        
            # Open the file if is a PNG image.
            image = None
            if(file.lower().endswith(('.png'))):
                image = pdb.file_png_load(inputPath, inputPath)
                
            # Verify if the file is an image.
            if(image != None):
                # Convert the image to greyscale.
                pdb.gimp_image_convert_grayscale(image)
                # Save the image.
                pdb.file_png_save(image, image.layers[0], outputPath, outputPath, 0, 9, 0, 0, 0, 0, 0)
        except Exception as err:
            gimp.message("Unexpected error: " + str(err))

register(
    "batch_token_invert",
    "Inverts the colors of the PNG token images in a folder",
    "Inverts the colors of the PNG token images in a folder",
    "JFM",
    "Open source (BSD 3-clause license)",
    "2013",
    "<Image>/Filters/BD18/Batch-token-invert",
    "",
    [
        (PF_DIRNAME, "inputFolder", "Input directory", ""),
        (PF_DIRNAME, "outputFolder", "Output directory", "")
    ],
    [],
    batch_token_invert)

main()
