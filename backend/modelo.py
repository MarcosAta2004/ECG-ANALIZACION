import tensorflow as tf
from tensorflow.keras.models import Model
from tensorflow.keras.layers import (
    Conv1D, MaxPooling1D, LSTM, Dense,
    Dropout, BatchNormalization, Bidirectional,
    Input, Concatenate,
)
from tensorflow.keras.optimizers import Adam
import config


def focal_loss(gamma=2.0, alpha=0.25):
    """
    Focal Loss para clasificación multiclase con desbalanceo.
    gamma: penaliza ejemplos fáciles (bien clasificados); >0 focaliza en difíciles.
    alpha: factor de escala global de la pérdida.
    """
    def loss_fn(y_true, y_pred):
        y_pred = tf.clip_by_value(y_pred, 1e-7, 1.0)
        ce = -y_true * tf.math.log(y_pred)
        pt = tf.reduce_sum(y_true * y_pred, axis=-1, keepdims=True)
        focal_weight = alpha * tf.pow(1.0 - pt, gamma)
        return tf.reduce_mean(focal_weight * ce)
    return loss_fn


def construir_modelo():
    print(">>> Construyendo Arquitectura CNN-LSTM + Metadata...")

    # ── Rama ECG (señal 1000×12) ──────────────────────────────────────────
    ecg_input = Input(shape=config.INPUT_SHAPE, name='ecg_input')

    x = Conv1D(64, 5, activation='relu')(ecg_input)
    x = BatchNormalization()(x)
    x = MaxPooling1D(2)(x)
    x = Dropout(0.3)(x)

    x = Conv1D(128, 3, activation='relu')(x)
    x = BatchNormalization()(x)
    x = MaxPooling1D(2)(x)
    x = Dropout(0.3)(x)

    x = Conv1D(256, 3, activation='relu')(x)
    x = BatchNormalization()(x)
    x = MaxPooling1D(2)(x)
    x = Dropout(0.3)(x)

    x = Bidirectional(LSTM(128, return_sequences=False))(x)
    x = Dropout(0.3)(x)
    # Reducido de Dense(128) a Dense(64) para equilibrar influencia con metadata
    ecg_features = Dense(64, activation='relu')(x)

    # ── Rama metadata (edad, sexo, peso) ──────────────────────────────────
    # Dense(32) para que la metadata tenga ~33% de peso en la fusión (64+32=96)
    meta_input = Input(shape=config.META_SHAPE, name='meta_input')
    m = Dense(32, activation='relu')(meta_input)
    m = BatchNormalization()(m)
    meta_features = Dense(32, activation='relu')(m)

    # ── Fusión y clasificación ────────────────────────────────────────────
    # ecg_features(64) + meta_features(32) = 96 dimensiones totales
    merged = Concatenate()([ecg_features, meta_features])
    merged = Dropout(0.3)(merged)
    output = Dense(config.NUM_CLASSES, activation='softmax')(merged)

    model = Model(inputs=[ecg_input, meta_input], outputs=output)

    model.compile(
        optimizer=Adam(learning_rate=0.0005),
        loss=focal_loss(gamma=2.0, alpha=0.25),
        metrics=[
            'accuracy',
            tf.keras.metrics.AUC(name='auc', multi_label=False),
        ]
    )
    return model
