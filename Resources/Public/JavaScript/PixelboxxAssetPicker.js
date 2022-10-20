define([
  "TYPO3/CMS/Recordlist/ElementBrowser",
  "TYPO3/CMS/Core/Event/RegularEvent",
  "TYPO3/CMS/Core/Ajax/AjaxRequest",
  "TYPO3/CMS/Core/DocumentService",
], function(ElementBrowser, RegularEvent, AjaxRequest, DocumentService) {
  "use strict";

  class PixelboxxAssetPicker {
    constructor() {
      this.storageUid = document.body.dataset.storageUid;
      this.assetPickerDomain = document.body.dataset.assetPickerDomain;

      DocumentService.ready().then(() => {
        window.addEventListener('message', this.onIframeMessage.bind(this), false);
      });
    }

    /**
     * @param {MessageEvent} event
     */
    onIframeMessage(event) {
      if (event.origin === `https://${this.assetPickerDomain}`) {
        return (new AjaxRequest(TYPO3.settings.ajaxUrls.import_from_asset_builder))
          .withQueryArguments({
            storageUid: this.storageUid
          })
          .post({}, {
            body: event.data
          })
          .then((response) => {
            response.resolve().then((result) => PixelboxxAssetPicker.insertElement(result.fileName, result.fileUid, false));
          });
      }
    }

    static insertElement(fileName, fileUid, close) {
      return ElementBrowser.insertElement('sys_file', String(fileUid), fileName, String(fileUid), close);
    }

  }
  return new PixelboxxAssetPicker;
});
