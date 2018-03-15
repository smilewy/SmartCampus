/**
 * 使用方式：
 * 在需要用到该文件数据的地方使用import导入，导入的时候有两种方式：
 *    a、只导入某一需要的数据，如只导入民族的数据：import { ethnics } from '/common-const-data'，
 *       该方式导入后的内容即为 ethnics 对应的数据。
 *
 *    b、将整个库的数据全部导入： import * as constData from /common-const-data'，
 *       导入后是一个对象，需要用 constData.ethnics 的方式获得相应数据
 *
 *
 * 其他说明：
 * 开发人员可以扩展该库，扩展的时候尽量用同样的方式进行定义，并写好注释
 *
 * 特别注意：该库最好只扩展常量类型
 */

/** *************************************************我是分割线*********************************************** */
/** *************************************************下面是正文*********************************************** */

/**
 * 民族
 */
export const ethnics = [
  {value:'汉族'},
  {value:'蒙古族'},
  {value:'回族'},
  {value:'藏族'},
  {value:'维吾尔族'},
  {value:'苗族'},
  {value:'彝族'},
  {value:'壮族'},
  {value:'布依族'},
  {value:'朝鲜族'},
  {value:'满族'},
  {value:'侗族'},
  {value:'瑶族'},
  {value:'白族'},
  {value:'土家族'},
  {value:'哈尼族'},
  {value:'哈萨克族'},
  {value:'傣族'},
  {value:'黎族'},
  {value:'僳僳族'},
  {value:'佤族'},
  {value:'畲族'},
  {value:'高山族'},
  {value:'拉祜族'},
  {value:'水族'},
  {value:'东乡族'},
  {value:'纳西族'},
  {value:'景颇族'},
  {value:'柯尔克孜族'},
  {value:'土族'},
  {value:'达斡尔族'},
  {value:'仫佬族'},
  {value:'羌族'},
  {value:'布朗族'},
  {value:'撒拉族'},
  {value:'毛南族'},
  {value:'仡佬族'},
  {value:'锡伯族'},
  {value:'阿昌族'},
  {value:'普米族'},
  {value:'塔吉克族'},
  {value:'怒族'},
  {value:'乌孜别克族'},
  {value:'俄罗斯族'},
  {value:'鄂温克族'},
  {value:'德昂族'},
  {value:'保安族'},
  {value:'裕固族'},
  {value:'京族'},
  {value:'塔塔尔族'},
  {value:'独龙族'},
  {value:'鄂伦春族'},
  {value:'赫哲族'},
  {value:'门巴族'},
  {value:'珞巴族'},
  {value:'基诺族'}
];

/**
 * 政治面貌
 */
export const politicalStatus = [
  { value: '共青团员' },
  { value: '党员' },
  { value: '群众' },
  { value: '其他' }
];

/**
 * 考生类型
 */
export const examineeType = [
  { value: '应届生' },
  { value: '复读生' },
  { value: '其他' }
];

/**
 * 身份证类型
 */
export const idCardType = [
  { value: '居民身份证' },
  { value: '香港特区护照/身份证' },
  { value: '澳门特区护照/身份证' },
  { value: '台湾居民来往大陆通行证' },
  { value: '境外永久居住证' },
  { value: '护照' },
  { value: '其他' }
];

/**
 * 残疾人类型
 */
export const disabledType = [
  { value: '视力残疾' },
  { value: '听力残疾' },
  { value: '言语残疾' },
  { value: '肢体残疾' },
  { value: '智力残疾' },
  { value: '精神残疾' },
  { value: '多重残疾' }
];

/**
 * 血型
 */
export const bloodType = [
  { value: 'A型' },
  { value: 'B型' },
  { value: 'O型' },
  { value: 'AB型' }
];

/**
 * 入学方式
 */
export const entranceType = [
  { value: '统一招生考试' },
  { value: '体育特招' },
  { value: '艺术特招' },
  { value: '其他' }
];

/**
 * 录取类别
 */
export const admittedType = [
  { value: '提前直升生' },
  { value: '提前特长批' },
  { value: '第一批A类正取' },
  { value: '第一批D类正取' },
  { value: '挂读' }
];

/**
 * 录取类别
 */
export const tenanciesType = [
  { value: '本区' },
  { value: '本市' },
  { value: '本省' },
  { value: '外省' }
];
