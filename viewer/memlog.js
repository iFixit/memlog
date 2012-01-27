function memlog(data) {
   var series = [];
   var details = {};
   for (var i = 0; i < data.length; i++) {
      var sample = data[i];

      var timestamp = sample[0] * 1000; // convert to ms
      var memory = sample[1];
      var file = sample[2];
      var klass = sample[3];
      var func = sample[4];

      series.push([timestamp, memory]);
      details[String(timestamp)] = {
         file: file,
         klass: klass,
         func: func
      };
   }                               

   // Helper functions for formatting. Thanks to:
   // http://dygraphs.com/tests/labelsKMB.html
   function round(num, places) {
      var shift = Math.pow(10, places);
      return Math.round(num * shift)/shift;
   }

   function formatBytes(v) {
      var suffixes = ['', 'k', 'M', 'G', 'T'];
      if (v < 1000) return v;
      
      var magnitude = Math.floor(String(Math.floor(v)).length / 3);
      if (magnitude > suffixes.length - 1) {
         magnitude = suffixes.length - 1;
      }

      return String(round(v / Math.pow(10, magnitude * 3), 2)) + suffixes[magnitude];
   }

   function formatMs(v) {
      return String(round(v, 3) + " ms");
   }

   var codeloc = $('codeloc');
   function showDetails(e, x) {
      var info = details[String(x)];
      var where = '';
      if (info.file) {
         where += '<strong>' + info.file + '</strong>:&nbsp;';
      }
      if (info.klass) {
         where += info.klass + '::';
      }
      if (info.func) {
         where += info.func + '()';
      }
      codeloc.set('html', where);
   }

   var graph = new Dygraph(document.getElementById('graph'), series, {
      width: 960,
      height: 300,
      fillGraph: true,
      stepPlot: true,
      labels: ['Time', 'Mem'],
      xlabel: 'Time (ms)',
      ylabel: 'Memory Usage',
      labelsKMG2: true,
      xValueFormatter: formatMs,
      yValueFormatter: formatBytes,
      highlightCallback: showDetails,
      colors: ["#336DA8"]
   });
}
