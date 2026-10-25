(function () {
  "use strict";

  var DEFAULT_DURATION = 850;
  var DEFAULT_WIDTH = 36;

  /**
   * Initialize one trail zone (scoped canvas + pointer listeners).
   *
   * Role:
   * Draws a fading white stroke over the zone using local coordinates.
   *
   * Inputs:
   * - zone: Root .wp-mouse-trail element containing a canvas.
   *
   * Outputs:
   * void (registers listeners and rAF loop)
   */
  function initZone(zone) {
    var canvas = zone.querySelector(".wp-mouse-trail__canvas");
    if (!canvas) {
      return;
    }

    var context = canvas.getContext("2d");
    if (!context) {
      return;
    }

    var trailDuration = parseInt(zone.getAttribute("data-duration"), 10);
    var trailWidth = parseInt(zone.getAttribute("data-width"), 10);

    if (!trailDuration || trailDuration < 1) {
      trailDuration = DEFAULT_DURATION;
    }
    if (!trailWidth || trailWidth < 1) {
      trailWidth = DEFAULT_WIDTH;
    }

    var animationFrame = 0;
    var points = [];
    var cssWidth = 0;
    var cssHeight = 0;

    var resizeCanvas = function () {
      var rect = zone.getBoundingClientRect();
      var pixelRatio = Math.min(window.devicePixelRatio || 1, 2);

      cssWidth = Math.max(1, Math.round(rect.width));
      cssHeight = Math.max(1, Math.round(rect.height));

      canvas.width = cssWidth * pixelRatio;
      canvas.height = cssHeight * pixelRatio;
      canvas.style.width = cssWidth + "px";
      canvas.style.height = cssHeight + "px";
      context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
    };

    var addPoint = function (event) {
      var rect = zone.getBoundingClientRect();
      var events = event.getCoalescedEvents
        ? event.getCoalescedEvents()
        : [event];

      for (var i = 0; i < events.length; i += 1) {
        var pointerEvent = events[i];
        points.push({
          x: pointerEvent.clientX - rect.left,
          y: pointerEvent.clientY - rect.top,
          createdAt: performance.now(),
        });
      }
    };

    var draw = function (now) {
      points = points.filter(function (point) {
        return now - point.createdAt < trailDuration;
      });

      context.clearRect(0, 0, cssWidth, cssHeight);
      context.lineCap = "round";
      context.lineJoin = "round";

      for (var index = 1; index < points.length; index += 1) {
        var previousPoint = points[index - 1];
        var point = points[index];
        var age = (now - point.createdAt) / trailDuration;

        context.beginPath();
        context.moveTo(previousPoint.x, previousPoint.y);
        context.lineTo(point.x, point.y);
        context.globalAlpha = Math.pow(Math.max(0, 1 - age), 2);
        context.lineWidth = Math.max(2, trailWidth * (1 - age));
        // White source color for black result over light content
        // while preserving the canvas difference blend mode.
        context.strokeStyle = "#ffffff";
        context.stroke();
      }

      context.globalAlpha = 1;
      animationFrame = window.requestAnimationFrame(draw);
    };

    var cleanup = function () {
      window.cancelAnimationFrame(animationFrame);
      zone.removeEventListener("pointermove", addPoint);
      window.removeEventListener("pagehide", cleanup);
      if (resizeObserver) {
        resizeObserver.disconnect();
      }
    };

    var resizeObserver = null;
    if (typeof ResizeObserver !== "undefined") {
      resizeObserver = new ResizeObserver(resizeCanvas);
      resizeObserver.observe(zone);
    } else {
      window.addEventListener("resize", resizeCanvas);
      var originalCleanup = cleanup;
      cleanup = function () {
        window.removeEventListener("resize", resizeCanvas);
        originalCleanup();
      };
    }

    resizeCanvas();
    zone.addEventListener("pointermove", addPoint, { passive: true });
    window.addEventListener("pagehide", cleanup);
    animationFrame = window.requestAnimationFrame(draw);
  }

  function boot() {
    var zones = document.querySelectorAll(".wp-mouse-trail");
    for (var i = 0; i < zones.length; i += 1) {
      initZone(zones[i]);
    }
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
