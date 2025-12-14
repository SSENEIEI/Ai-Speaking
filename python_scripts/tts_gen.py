import sys
import asyncio
import edge_tts
import json
import base64
import os
import tempfile

# Ensure stdout uses UTF-8
sys.stdout.reconfigure(encoding='utf-8')

async def generate_audio(text, voice="th-TH-PremwadeeNeural"):
    try:
        # Create a temporary file to store the audio
        with tempfile.NamedTemporaryFile(suffix=".mp3", delete=False) as temp_file:
            output_file = temp_file.name

        communicate = edge_tts.Communicate(text, voice)
        await communicate.save(output_file)

        # Read the file and encode to base64
        with open(output_file, "rb") as f:
            audio_data = f.read()
            audio_base64 = base64.b64encode(audio_data).decode('utf-8')

        # Clean up the temporary file
        os.remove(output_file)

        # Output JSON result
        result = {
            "success": True,
            "audioContent": audio_base64
        }
        print(json.dumps(result))

    except Exception as e:
        error_result = {
            "success": False,
            "error": str(e)
        }
        print(json.dumps(error_result))

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"success": False, "error": "No text provided"}))
        sys.exit(1)

    text_to_speak = sys.argv[1]
    # Run the async function
    asyncio.run(generate_audio(text_to_speak))
