/* Geometria pura do encaixe do botão JarvisDevIN, sem dependência do DOM. */
(function (host, factory) {
  'use strict';
  const docking = factory();
  if (typeof module === 'object' && module.exports) module.exports = docking;
  if (host) host.DevInJarvisDock = docking;
})(typeof window === 'undefined' ? null : window, function () {
  'use strict';
  const MARGIN = 12;
  function clamp(value, low, high) {
    return Math.max(low, Math.min(high, value));
  }
  function snap(centerX, desiredTop, viewportWidth, viewportHeight, width, height, margin = MARGIN) {
    const safeWidth = Math.max(1, viewportWidth);
    const safeHeight = Math.max(1, viewportHeight);
    const safeMargin = Math.max(0, margin);
    const maxLeft = Math.max(0, safeWidth - Math.max(0, width) - safeMargin);
    const maxTop = Math.max(0, safeHeight - Math.max(0, height) - safeMargin);
    const leftEdge = Math.min(safeMargin, maxLeft);
    const rightEdge = Math.max(leftEdge, maxLeft);
    const minTop = Math.min(safeMargin, maxTop);
    const side = centerX < safeWidth / 2 ? 'left' : 'right';
    const top = clamp(desiredTop, minTop, maxTop);
    const range = maxTop - minTop;
    return {
      side,
      left: side === 'left' ? leftEdge : rightEdge,
      top,
      yRatio: range > 0 ? (top - minTop) / range : 0
    };
  }
  function restore(saved, viewportWidth, viewportHeight, width, height, margin = MARGIN) {
    const side = saved && saved.side === 'right' ? 'right' : 'left';
    const ratio = saved && Number.isFinite(saved.yRatio)
      ? clamp(saved.yRatio, 0, 1) : 1;
    const maxTop = Math.max(0, viewportHeight - height - margin);
    const minTop = Math.min(margin, maxTop);
    const top = minTop + ratio * (maxTop - minTop);
    return snap(side === 'right' ? viewportWidth : 0,
      top, viewportWidth, viewportHeight, width, height, margin);
  }
  return {snap, restore, clamp, MARGIN};
});
