from flask import Flask, request, jsonify
from ultralytics import YOLO
import cv2
import numpy as np
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

# Define a dictionary to map class identifiers to their labels
class_mapping = {
    0: 'Coca cola',
    1: 'Indomie Ayam Bawang',
    2: 'Indomie Goreng',
    3: 'You C 1000 Lemon Water'
}

def run_prediction(model_path, image):
    # Load the trained model
    model = YOLO(model_path)

    # Run prediction on the image data
    results = model.predict(source=image, save=False, save_txt=False)

    # Initialize variables to keep track of the highest confidence
    max_confidence = 0
    best_label = None

    # Collect detection results and find the one with the highest confidence
    for bbox in results[0].boxes:
        confidence = bbox.conf.item()  # Access confidence attribute correctly
        print(confidence)
        print(bbox.cls.item())
        # Update the best label if current confidence is higher
        if confidence > max_confidence:
            max_confidence = confidence
            best_label = bbox.cls.item()  # Access label attribute correctly

    # If the highest confidence is less than 0.8, set label to "Unknown"
    if max_confidence < 0.7:
        best_label = None

    # Map the numerical class identifier to its label
    if best_label is not None:
        label_name = class_mapping.get(best_label, 'Unknown')
    else:
        label_name = 'Unknown'
    print("Best label:", label_name)
    return label_name

@app.route('/predict', methods=['POST'])
def predict():
    # Check if the POST request has the file part
    if 'file' not in request.files:
        return jsonify({'error': 'No file part'})

    file = request.files['file']

    # Check if the file is missing
    if file.filename == '':
        return jsonify({'error': 'No selected file'})

    # Check if the file is allowed
    if file:
        image = cv2.imdecode(np.fromstring(file.read(), np.uint8), cv2.IMREAD_COLOR)
        model_path = 'best (8).pt'
        detection_label = run_prediction(model_path, image)
        return jsonify({'detection_label': detection_label})

if __name__ == '__main__':
    app.run(debug=True)
