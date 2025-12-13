{{-- resources/views/presenca/scanner.blade.php --}}

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Scanner de Presença - Teste Câmera + QR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 1rem;
            background: #f5f5f5;
        }

        h2 {
            margin-bottom: 0.5rem;
        }

        #preview {
            width: 100%;
            max-width: 400px;
            aspect-ratio: 3 / 4;
            background: #000;
            display: block;
        }

        #debug {
            margin-top: 1rem;
            padding: 0.5rem;
            background: #fff;
            border: 1px solid #ccc;
            font-size: 0.85rem;
            white-space: pre-wrap;
        }

        button {
            margin-top: 1rem;
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body>
    <h2>Scanner de Presença</h2>
    <p>Aponte a câmera para o QR Code do funcionário.</p>

    {{-- Vídeo com a câmera --}}
    <video id="preview" autoplay playsinline></video>

    {{-- Canvas escondido para capturar o frame e ler o QR --}}
    <canvas id="qr-canvas" style="display:none;"></canvas>

    <div id="debug"></div>

    <button type="button" onclick="window.location.href='{{ route('dashboard') }}'">
        Voltar ao Dashboard
    </button>

    {{-- Biblioteca de leitura de QR (só decoder, não mexe na câmera) --}}
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

    <script>
        const debugEl = document.getElementById('debug');
        const videoEl = document.getElementById('preview');
        const canvasEl = document.getElementById('qr-canvas');
        const canvasCtx = canvasEl.getContext('2d');

        // prefixo da URL que deve estar dentro do QR
        const basePresenca = "{{ url('/presenca/qr') }}/";

        let streamGlobal = null;
        let qrLido = false;

        function log(msg) {
            debugEl.textContent += msg + '\n';
        }

        // Mostra informações do contexto
        log('location.protocol = ' + location.protocol);
        log('location.hostname = ' + location.hostname);
        log('window.isSecureContext = ' + (window.isSecureContext ? 'true' : 'false'));

        async function startCamera() {
            try {
                if (!window.isSecureContext) {
                    log('AVISO: contexto NÃO é seguro (Chrome só permite câmera em HTTPS ou localhost).');
                } else {
                    log('Contexto seguro detectado. (HTTPS ou localhost)');
                }

                const constraints = {
                    video: {
                        facingMode: 'environment' // câmera traseira no celular
                    }
                };

                const stream = await navigator.mediaDevices.getUserMedia(constraints);
                streamGlobal = stream;
                videoEl.srcObject = stream;

                log('Câmera iniciada com sucesso.');
                // Quando o vídeo estiver pronto, iniciamos o loop de leitura do QR
                videoEl.addEventListener('loadedmetadata', () => {
                    iniciarLeituraQr();
                });

            } catch (err) {
                log('Erro ao acessar a câmera: ' + err.name + ' - ' + err.message);
            }
        }

        function pararCamera() {
            if (streamGlobal) {
                streamGlobal.getTracks().forEach(t => t.stop());
                streamGlobal = null;
                log('Câmera parada.');
            }
        }

        function iniciarLeituraQr() {
            // Ajusta o tamanho do canvas para o tamanho do vídeo
            canvasEl.width  = videoEl.videoWidth;
            canvasEl.height = videoEl.videoHeight;

            log('Iniciando loop de leitura de QR...');
            requestAnimationFrame(loopLeituraQr);
        }

        function loopLeituraQr() {
            if (qrLido) {
                return; // já tratamos um QR, não continua
            }

            if (videoEl.readyState === videoEl.HAVE_ENOUGH_DATA) {
                // Desenha o frame atual do vídeo no canvas
                canvasCtx.drawImage(videoEl, 0, 0, canvasEl.width, canvasEl.height);
                const imageData = canvasCtx.getImageData(0, 0, canvasEl.width, canvasEl.height);

                const qrCode = jsQR(imageData.data, canvasEl.width, canvasEl.height, {
                    inversionAttempts: 'dontInvert',
                });

                if (qrCode) {
                    const decodedText = qrCode.data;
                    log('QR lido: ' + decodedText);

                    // Marca que já leu para não repetir
                    qrLido = true;

                    // Opcional: mostrar visualmente que vai redirecionar
                    log('Validando QR e redirecionando...');

                    if (!decodedText.startsWith(basePresenca)) {
                        log('QR inválido para este sistema. Esperado prefixo: ' + basePresenca);
                        alert('QR Code inválido para este sistema.');
                        pararCamera();
                        return;
                    }

                    // Para a câmera e redireciona para a URL que está dentro do QR
                    pararCamera();
                    window.location.href = decodedText;
                    return;
                }
            }

            // Se ainda não leu nada, agenda o próximo frame
            if (!qrLido) {
                requestAnimationFrame(loopLeituraQr);
            }
        }

        document.addEventListener('DOMContentLoaded', startCamera);
    </script>
</body>
</html>
