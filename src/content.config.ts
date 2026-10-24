// Content collections (docs/21 mục 4): mỗi thư mục trong content/ là một collection YAML.
// Schema Zod sinh từ scripts/lib/schema.mjs (`npm run sinh:schema`); quy tắc R01–R22 kiểm trước build bởi validator.
import { defineCollection } from 'astro:content';
import { glob } from 'astro/loaders';
import { SCHEMA_ZOD, type TenCollection } from './sinh-ra/schema';
import { CO_BO_MAU, THU_MUC_MAU } from './lib/moi-truong';

// id của mục = tên tệp (cd-0001), không phải trường `slug` như mặc định của glob loader.
// Bộ mẫu giao diện (D-050) chỉ nạp thêm khi không phải bản chính thức; mã của nó (cd-91xx, src-mau-…) không trùng dữ liệu thật.
// (Khai từng collection riêng: bọc defineCollection trong hàm chung làm mất kiểu của schema.)
const tuThuMuc = (ten: TenCollection) =>
  glob({
    base: '.',
    pattern: [`content/${ten}/*.yaml`, ...(CO_BO_MAU ? [`${THU_MUC_MAU}/${ten}/*.yaml`] : [])],
    generateId: ({ entry }) => entry.replace(/^.*\//, '').replace(/\.yaml$/, ''),
  });

export const collections = {
  'tac-pham': defineCollection({ loader: tuThuMuc('tac-pham'), schema: SCHEMA_ZOD['tac-pham'] }),
  'muc-tu': defineCollection({ loader: tuThuMuc('muc-tu'), schema: SCHEMA_ZOD['muc-tu'] }),
  'mo-tip': defineCollection({ loader: tuThuMuc('mo-tip'), schema: SCHEMA_ZOD['mo-tip'] }),
  'nguon': defineCollection({ loader: tuThuMuc('nguon'), schema: SCHEMA_ZOD['nguon'] }),
  'tinh-huong': defineCollection({ loader: tuThuMuc('tinh-huong'), schema: SCHEMA_ZOD['tinh-huong'] }),
  'ghi-am': defineCollection({ loader: tuThuMuc('ghi-am'), schema: SCHEMA_ZOD['ghi-am'] }),
  'nguoi-dong-gop': defineCollection({ loader: tuThuMuc('nguoi-dong-gop'), schema: SCHEMA_ZOD['nguoi-dong-gop'] }),
};
