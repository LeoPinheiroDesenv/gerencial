;(function(window){
    // Garante que o namespace exista
    if (!window.qz) window.qz = {};
    if (!qz.image) qz.image = {};
  
    /**
     * qz.image.load(dataURL: string) → Promise<printJob>
     *
     * Converte um dataURL (base64 png) num objeto de impressão
     * ESC/POS raster, usando GS v 0 (raster bit image).
     */
    qz.image.load = function(dataURL) {
      return new Promise(function(resolve, reject) {
        var img = new Image();
        img.onload = function() {
          // desenha no canvas temporário
          var canvas = document.createElement('canvas');
          canvas.width  = img.width;
          canvas.height = img.height;
          var ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0);
  
          try {
            // gera o printJob a partir do canvas
            var job = qz.image.toRaster(canvas);
            resolve(job);
          } catch (e) {
            reject(e);
          }
        };
        img.onerror = function(err) {
          reject(err);
        };
        img.src = dataURL;
      });
    };
  
    /**
     * qz.image.toRaster(canvas: HTMLCanvasElement) → { type, format, data }
     *
     * Extrai os pixels do canvas e monta o comando ESC/POS:
     *   GS v 0 m xL xH yL yH <bitmap data>
     */
    qz.image.toRaster = function(canvas) {
      var w = canvas.width,
          h = canvas.height,
          ctx = canvas.getContext('2d'),
          img = ctx.getImageData(0, 0, w, h).data,
          bytes = [];
  
      for (var y = 0; y < h; y += 24) {
        // cabeçalho GS v 0 m=0 (raster bitmap)
        bytes.push(
          0x1D, 0x76, 0x30, 0x00,
          w & 0xFF, (w >> 8) & 0xFF,
          ((h - y) & 0xFF), (((h - y) >> 8) & 0xFF)
        );
  
        // para cada coluna de x
        for (var x = 0; x < w; x++) {
          // dividimos em 3 “fatias” de 8 pixels verticais
          for (var slice = 0; slice < 3; slice++) {
            var byteVal = 0;
            // cada bit é um pixel (1 = preto, 0 = branco)
            for (var bit = 0; bit < 8; bit++) {
              var yy = y + slice * 8 + bit;
              if (yy < h) {
                var idx = (yy * w + x) * 4;
                // testa intensidade no canal vermelho (rgb são iguais para png B&N)
                if (img[idx] < 128) {
                  byteVal |= (1 << (7 - bit));
                }
              }
            }
            bytes.push(byteVal);
          }
        }
      }
  
      // converte array de bytes em string raw
      var dataStr = String.fromCharCode.apply(null, bytes);
  
      // formato para QZ Tray
      return {
        type:   'raw',
        format: 'command',
        data:   dataStr
      };
    };
  
    // aliases
    qz.image.convert     = qz.image.load;
    qz.image.getRawImage = qz.image.load;
  
  })(window);
  