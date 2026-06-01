import tensorflow as tf
from tensorflow.keras.layers import (
    BatchNormalization,
    Bidirectional,
    Conv1D,
    Dense,
    Dropout,
    Input,
    LSTM,
    MaxPooling1D,
)
from tensorflow.keras.models import Model
from tensorflow.keras.optimizers import Adam

import config


def focal_loss(gamma=2.0, alpha=0.25):
    """Focal loss usada durante el entrenamiento del modelo."""
    def loss_fn(y_true, y_pred):
        y_pred = tf.clip_by_value(y_pred, 1e-7, 1.0)
        ce = -y_true * tf.math.log(y_pred)
        pt = tf.reduce_sum(y_true * y_pred, axis=-1, keepdims=True)
        focal_weight = alpha * tf.pow(1.0 - pt, gamma)
        return tf.reduce_mean(focal_weight * ce)

    return loss_fn


def construir_modelo(input_shape=None):
    print(">>> Construyendo arquitectura CNN-LSTM ECG-only...")

    if input_shape is None:
        input_shape = config.INPUT_SHAPE

    ecg_input = Input(shape=input_shape, name="ecg_input")

    x = Conv1D(64, 5, activation="relu")(ecg_input)
    x = BatchNormalization()(x)
    x = MaxPooling1D(2)(x)
    x = Dropout(0.3)(x)

    x = Conv1D(128, 3, activation="relu")(x)
    x = BatchNormalization()(x)
    x = MaxPooling1D(2)(x)
    x = Dropout(0.3)(x)

    x = Conv1D(256, 3, activation="relu")(x)
    x = BatchNormalization()(x)
    x = MaxPooling1D(2)(x)
    x = Dropout(0.3)(x)

    x = Bidirectional(LSTM(128, return_sequences=False))(x)
    x = Dropout(0.3)(x)

    x = Dense(64, activation="relu")(x)
    x = Dropout(0.3)(x)
    output = Dense(config.NUM_CLASSES, activation="softmax")(x)

    model = Model(inputs=ecg_input, outputs=output)

    model.compile(
        optimizer=Adam(learning_rate=0.0005),
        loss=focal_loss(gamma=2.0, alpha=0.25),
        metrics=[
            "accuracy",
            tf.keras.metrics.AUC(name="auc", multi_label=False),
        ],
    )
    return model
