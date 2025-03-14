import cv2
from urllib.request import urlretrieve

def detect_face(image_face):
    net = cv2.dnn.readNetFromCaffe("deploy.prototxt", "res10_300x300_ssd_iter_140000_fp16.caffemodel")
    # Model parameters
    in_width = 300
    in_height = 300
    mean = [104, 117, 123]
    # conf_threshold = 0.7

    # Create a 4D blob from a frame.
    blob = cv2.dnn.blobFromImage(image_face, 1.0, (in_width, in_height), mean, swapRB=False, crop=False)
    # Run a model
    net.setInput(blob)
    detections = net.forward()

    return detections