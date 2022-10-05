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

      DocumentService.ready().then(() => {
      });
    }
  }
  return new PixelboxxAssetPicker;
});
