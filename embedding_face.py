import sys
import cv2
import numpy as np
import os
from urllib.request import urlretrieve
from detect import detect_face

# Tải model nếu chưa có
model_url = "https://github.com/deepinsight/insightface/releases/download/v0.7/buffalo_l.zip"
model_path = "w600k_mbf.onnx"

if not os.path.exists(model_path):
    print("Downloading model...")
    urlretrieve(model_url, "model.zip")
    os.system("unzip model.zip")

# Load model
net = cv2.dnn.readNetFromONNX(model_path)

def get_embedding(image,i=0):
    """
    Get face embedding from image
    Args:
        image: can be either path to image file or numpy array from camera
    Returns:
        embedding vector
    """
    # Nếu input là đường dẫn, đọc ảnh
    if isinstance(image, str):
        if not os.path.exists(image):
            image = "uploads/" + image
        image = cv2.imread(image)
    
    if image is None:
        raise Exception("Cannot read image")
    
    frame_height = image.shape[0]
    frame_width = image.shape[1]
    
    result = detect_face(image)
    
    x_top_left = int(result[0, 0, i, 3] * frame_width)
    y_top_left = int(result[0, 0, i, 4] * frame_height)
    x_bottom_right  = int(result[0, 0, i, 5] * frame_width)
    y_bottom_right  = int(result[0, 0, i, 6] * frame_height)

    face_crop = image[y_top_left:y_bottom_right, x_top_left:x_bottom_right]

    # Resize và chuẩn hóa ảnh
    blob = cv2.dnn.blobFromImage(face_crop, 1.0/127.5, (112, 112), (127.5, 127.5, 127.5), swapRB=True)
    
    # Tính toán embedding
    net.setInput(blob)
    embedding = net.forward()
    
    # Chuẩn hóa L2
    embedding = embedding / np.linalg.norm(embedding)
    return embedding

def main():
    if len(sys.argv) != 2:
        print("Usage: python embedding_face.py <image_file>")
        sys.exit(1)

    try:
        embedding = get_embedding(sys.argv[1])
        print(','.join(map(str, embedding.flatten().tolist())))
    except Exception as e:
        print(f"Error: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()
