import wave
import math
import struct

# Create a simple 1-second sine wave audio file
sample_rate = 24000
duration = 2.0 # seconds
frequency = 440.0 # Hz (A4)

file_path = "ref_audio.wav"

with wave.open(file_path, 'w') as wav_file:
    wav_file.setnchannels(1) # Mono
    wav_file.setsampwidth(2) # 2 bytes per sample (16-bit)
    wav_file.setframerate(sample_rate)
    
    num_samples = int(sample_rate * duration)
    for i in range(num_samples):
        value = int(32767.0 * math.sin(2.0 * math.pi * frequency * i / sample_rate))
        data = struct.pack('<h', value)
        wav_file.writeframes(data)

print(f"Created dummy {file_path}")
