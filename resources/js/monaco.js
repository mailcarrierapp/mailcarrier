import 'monaco-editor/esm/vs/editor/editor.api';

window.MonacoEnvironment = {
  getWorkerUrl: (moduleId, label) => {
    if (label === 'html') {
      return '/js/html.worker.js';
    }

    if (label === 'json') {
      return '/js/json.worker.js';
    }

    return '/js/editor.worker.js';
  },
  getWorker: (moduleId, label) => {
    return new Worker(window.MonacoEnvironment.getWorkerUrl(moduleId, label));
  },
};
