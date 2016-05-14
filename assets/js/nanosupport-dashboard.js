/**!
 * NanoSupport Dashboard Scripts
 * Scripts to decorate/manipulate NanoSupport Dashboard widget.
 *
 * @since   1.0.0
 *
 * @author  nanodesigns
 * @package NanoSupport
 */
var chart = c3.generate({
    bindto: '#ns-chart',
    data: {
        columns: [
            [ns.inspection_label, ns.inspection],
            [ns.open_label, ns.open],
            [ns.solved_label, ns.solved],
            [ns.pending_label, ns.pending],
        ],
        type : 'donut',
    },
    donut: {
        width: 45,
        label: {
            format: function (value, ratio, id) {
              return d3.format('')(value);
            }
        }
    },
    color: {
        pattern: [
            '#3bafda',  //Inspection
            '#f6bb42',  //Open
            '#8cc152',  //Solved
            '#aab2bd'   //Pending
        ]
    },
    size: {
        height: 200
    }
});