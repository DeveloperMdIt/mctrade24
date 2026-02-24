const standaloneButtonTemplate = ({id, fundingSource, layout}) =>
`<div data-funding-source="${fundingSource}" id="${id}" class="${layout === 'horizontal' ? 'col-sm-6 ' : 'col-sm-12 '}ppc-standalone-buttons"></div>`;
