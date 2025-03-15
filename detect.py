import cv2
import numpy as np
from urllib.request import urlretrieve
import os
from zipfile import ZipFile
import sys

# ========================-Downloading Assets-========================
def download_and_unzip(url, save_path):
    print(f"Downloading and extracting assets....", end="")

    # Downloading zip file using urllib package.
    urlretrieve(url, save_path)

    try:
        # Extracting zip file using the zipfile package.
        with ZipFile(save_path) as z:
            # Extract ZIP file contents in the same directory.
            z.extractall(os.path.split(save_path)[0])

        print("Done")

    except Exception as e:
        print("\nInvalid file.", e)

URL = r"https://www.dropbox.com/s/efitgt363ada95a/opencv_bootcamp_assets_12.zip?dl=1"

def detect_face(image_face):

    asset_zip_path = os.path.join(os.getcwd(), f"opencv_bootcamp_assets_12.zip")

    # Download if asset ZIP does not exist.
    if not os.path.exists(asset_zip_path):
        download_and_unzip(URL, asset_zip_path)
    # ====================================================================

    net = cv2.dnn.readNetFromCaffe("deploy.prototxt", "res10_300x300_ssd_iter_140000_fp16.caffemodel")
    # Model parameters
    in_width = 300
    in_height = 300
    mean = [104, 117, 123]
    conf_threshold = 0.7

    if isinstance(image_face, str):
        image_face = cv2.imread(image_face)

    # Check if the image file exists
    # if not os.path.exists(image_path):
    #     raise ValueError(f"Image file {image_path} does not exist.")

    # Read the image
    # image_face = cv2.imread(image_path)
    if image_face is None:
        raise ValueError("Could not read the image.")

    # Create a 4D blob from a frame.
    blob = cv2.dnn.blobFromImage(image_face, 1.0, (in_width, in_height), mean, swapRB=False, crop=False)
    # Run a model
    net.setInput(blob)
    detections = net.forward()

    h, w = image_face.shape[:2]
    faces = []
    corners = []

    for i in range(detections.shape[2]):
        confidence = detections[0, 0, i, 2]
        if confidence > conf_threshold:
            box = detections[0, 0, i, 3:7] * np.array([w, h, w, h])
            (x1, y1, x2, y2) = box.astype("int")
            face = image_face[y1:y2, x1:x2]
            faces.append(face)
            corners.append((x1, y1, x2, y2))

    return faces, corners

def main():
    if len(sys.argv) != 2:
        print("Usage: python detect.py <image_file>")
        sys.exit(1)

    try:
        faces, _ = detect_face(sys.argv[1])
        if faces:
            # Chỉ lấy khuôn mặt đầu tiên và lưu vào tệp
            face = faces[0]
            face_path = "face.jpg"
            cv2.imwrite(face_path, face)
            print(face_path)
        else:
            print("No faces detected.")
    except Exception as e:
        print(f"Error: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()