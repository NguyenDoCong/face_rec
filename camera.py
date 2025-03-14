import os
import cv2
import sys
import numpy as np
from zipfile import ZipFile
from urllib.request import urlretrieve
from embedding_face import get_embedding
import requests
from detect import detect_face

# ========================-Downloading Assets-========================
def download_and_unzip(url, save_path):
    print(f"Downloading and extracting assests....", end="")

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

asset_zip_path = os.path.join(os.getcwd(), f"opencv_bootcamp_assets_12.zip")

# Download if assest ZIP does not exists.
if not os.path.exists(asset_zip_path):
    download_and_unzip(URL, asset_zip_path)
# ====================================================================


s = 0
if len(sys.argv) > 1:
    s = sys.argv[1]

source = cv2.VideoCapture(s)

win_name = "Camera Preview"
cv2.namedWindow(win_name, cv2.WINDOW_NORMAL)

net = cv2.dnn.readNetFromCaffe("deploy.prototxt", "res10_300x300_ssd_iter_140000_fp16.caffemodel")
# Model parameters
in_width = 300
in_height = 300
mean = [104, 117, 123]
conf_threshold = 0.7

while cv2.waitKey(1) != 27:
    has_frame, frame = source.read()
    if not has_frame:
        break
    frame = cv2.flip(frame, 1)
    frame_height = frame.shape[0]
    frame_width = frame.shape[1]

    # # Create a 4D blob from a frame.
    # blob = cv2.dnn.blobFromImage(frame, 1.0, (in_width, in_height), mean, swapRB=False, crop=False)
    # # Run a model
    # net.setInput(blob)
    # detections = net.forward()

    detections = detect_face(frame)

    for i in range(detections.shape[2]):
        confidence = detections[0, 0, i, 2]
        if confidence > conf_threshold:
            x_top_left = int(detections[0, 0, i, 3] * frame_width)
            y_top_left = int(detections[0, 0, i, 4] * frame_height)
            x_bottom_right  = int(detections[0, 0, i, 5] * frame_width)
            y_bottom_right  = int(detections[0, 0, i, 6] * frame_height)

            # face_crop = frame[y_top_left:y_bottom_right, x_top_left:x_bottom_right]
            face_embedding = get_embedding(frame,i)

            # Kiểm tra giá trị của face_embedding
            if face_embedding is None:
                print("Error: face_embedding is None")
                continue

            print("face_embedding:", face_embedding)

            payload = {"embedding": face_embedding.tolist()}
            try:
                response = requests.post("http://localhost/face_rec/index.php?controller=Student&action=check", json=payload)
                print("Response status:", response.status_code)
                print("Response content:", response.content)
                if response.content:
                    try:
                        result = response.json()
                        if result['success']:
                            student = result['student']
                            cv2.rectangle(frame, (x_top_left, y_top_left), (x_bottom_right, y_bottom_right), (0, 255, 0))
                            label = f"{student['name']} ({student['student_id']}) - {student['similarity']:.4f}"
                            label_size, base_line = cv2.getTextSize(label, cv2.FONT_HERSHEY_SIMPLEX, 0.5, 1)

                            cv2.rectangle(
                            frame,
                            (x_top_left, y_top_left - label_size[1]),
                            (x_top_left + label_size[0], y_top_left + base_line),
                            (255, 255, 255),
                            cv2.FILLED,
                            )
                            cv2.putText(frame, label, (x_top_left, y_top_left), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 0, 0))
                    except ValueError as e:
                        print("Error parsing JSON response:", str(e))
                else:
                    print("Empty response")
            except Exception as e:
                print("Error:", str(e))

    # t, _ = net.getPerfProfile()
    # label = "Inference time: %.2f ms" % (t * 1000.0 / cv2.getTickFrequency())
    cv2.putText(frame, label, (0, 15), cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 0))
    cv2.imshow(win_name, frame)

source.release()
cv2.destroyWindow(win_name)