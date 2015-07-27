define([
  'summernote/core/list',
  'summernote/core/dom',
  'summernote/core/agent'
], function (list, dom, agent) {
  var Clipboard = function (handler) {

    var $paste;

    this.attach = function (layoutInfo) {

      if (window.clipboardData || agent.isFF) {
        $paste = $('<div />').attr('contenteditable', true).css({
          position : 'absolute',
          left : -100000,
          'opacity' : 0
        });
        layoutInfo.editable().after($paste);
        $paste.one('paste', hPasteClipboardImage);
        
        layoutInfo.editable().on('keydown', function (e) {
          if (e.ctrlKey && e.keyCode === 86) {  // CTRL+V
            handler.invoke('saveRange', layoutInfo.editable());
            if ($paste) {
              $paste.focus();
            }
          }
        });
      }

      layoutInfo.editable().on('paste', hPasteClipboardImage);
    };

    /**
     * paste clipboard image
     *
     * @param {Event} event
     */
    var hPasteClipboardImage = function (event) {

      var clipboardData = event.originalEvent.clipboardData;
      var layoutInfo = dom.makeLayoutInfo(event.currentTarget || event.target);
      var $editable = layoutInfo.editable();

      if (!clipboardData || !clipboardData.items || !clipboardData.items.length) {

        var callbacks = $editable.data('callbacks');
        // only can run if it has onImageUpload method
        if (!callbacks.onImageUpload) {
          return;
        }

        setTimeout(function () {
          if (!$paste) {
            return;
          }

          var imgNode = $paste[0].firstChild;
          if (!imgNode) {
            return;
          }

          handler.invoke('restoreRange', $editable);
          if (!dom.isImg(imgNode)) {
            handler.invoke('pasteHTML', $editable, $paste.html());
          } else {
            var datauri = imgNode.src;

            var data = atob(datauri.split(',')[1]);
            var array = new Uint8Array(data.length);
            for (var i = 0; i < data.length; i++) {
              array[i] = data.charCodeAt(i);
            }

            var blob = new Blob([array], { type : 'image/png' });
            blob.name = 'clipboard.png';
            handler.invoke('focus', $editable);
            handler.insertImages(layoutInfo, [blob]);
          }

          $paste.remove();

        }, 0);

        return;
      }

      var item = list.head(clipboardData.items);
      var isClipboardImage = item.kind === 'file' && item.type.indexOf('image/') !== -1;

      if (isClipboardImage) {
        handler.insertImages(layoutInfo, [item.getAsFile()]);
      }

      handler.invoke('editor.afterCommand', $editable);
    };
  };

  return Clipboard;
});
