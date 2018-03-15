<template>
  <div class="g-container StudentInformationInquiry">
    <header class="g-header">
      <div class="gh-header">信息管理</div>
      <div class="gh-section notRequire clear_fix">
        <el-form ref="studentMessge" :rules="studentMsgRules" :model="dataHeader"
                 label-width="86px">
          <el-form-item label="年级：" prop="grade">
            <el-select class="g-select" @change="gradeClick" v-model="dataHeader.grade" placeholder="请选择年级">
              <el-option v-for="(content,index) in headerButtonData.gradeloadData"
                         :label="content.znGradeName"
                         :key="content.gradeid"
                         :value="content.gradeid"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="班级：" prop="classes">
            <el-select class="g-select" v-model="dataHeader.classes" placeholder="请选择班级">
              <el-option label="全选" value="all" :disabled="!headerButtonData.classesLoadData.length>0"></el-option>
              <el-option v-for="(content,index) in headerButtonData.classesLoadData"
                         :key='index' :label="content.classname"
                         :value="content.classid"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="信息类别：" prop="messageType">
            <el-select class="g-select" v-model="dataHeader.messageType" placeholder="请选择信息类别">
              <el-option v-for="(content,index) in headerButtonData.msgTypeLoadData" :key="index" :label="content.name"
                         :value="content.ListType"></el-option>
            </el-select>
          </el-form-item>
        </el-form>
      </div>
      <div class="gh-buttonGroup clear_fix">
        <el-button type="primary" @click="searchClick('studentMessge',0)">查询</el-button>
      </div>
    </header>
    <section class="g-section">
      <div class="gs-header">
        <div class="gs-button alertsBtn">
          <el-button-group>
            <el-button @click="buttonClick" data-msg="export" class="filt" title="导出">
              <img class="filt_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out.png"/>
              <img class="filt_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_out_highlight.png"/>
            </el-button>
            <el-button  data-msg="print" class="filt" title="打印预览"  @click="operationData('print')">
              <img class="filt_unactive"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin.png"/>
              <img class="filt_active"
                   src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_dayin_highlight.png"/>
            </el-button>
            <!--<el-button @click="buttonClick" data-msg="delete" class="filt" title="删除">-->
            <!--<img class="filt_unactive"-->
            <!--src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete.png"/>-->
            <!--<img class="filt_active"-->
            <!--src="../../../../assets/img/schManagementSystem/baseSettings/userManager/teacher/icon_delete_highlight.png"/>-->
            <!--</el-button>-->
          </el-button-group>
          <el-button-group class="elGroupButton_two">
          </el-button-group>
        </div>
        <div class="gs-refresh g-fuzzyInput">
          <el-input type="text" v-model="fuzzyInput" suffix-icon="el-icon-search" placeholder="班级/姓名"
                    @change="fuzzyClick"></el-input>
        </div>
      </div>
      <div class="gs-table alertsList">
        <el-table
          v-loading.body="isLoading"
          element-loading-text="拼命加载中..."
          class="g-fixedColumn" v-show="headerButtonData.hasBasicData" ref="studentMsgTable"
                  :data="headerButtonData.studentBasicMsg" style="width:100%" @sort-change="sortChange"
                  @selection-change="handleStudentTable">
          <!--show-overflow-tooltip 超出部分省略号显示-->
          <!--<el-table-column type="selection"></el-table-column>-->
          <el-table-column label="序号" width="110" type="index"></el-table-column>
          <el-table-column min-width="180" v-for="(content,index) in headerButtonData.studentBasicTr" :key="index"
                           :label="content.zh"
                           :prop="content.en" sortable></el-table-column>
          <!--<el-table-column label="操作" fixed="right" width="80">-->
          <!--<template slot-scope="scope">-->
          <!--<el-button @click="changeBasicMsg(scope.$index)" type="text">修改</el-button>-->
          <!--</template>-->
          <!--</el-table-column>-->
        </el-table>
      </div>
    </section>
    <footer v-show="headerButtonData.hasBasicData" class="g-footer">
      <el-row class="pageAlerts">
        <el-pagination
          @current-change="handleCurrentChange"
          :current-page.sync="currentPage"
          layout="prev, pager, next, jumper"
          :page-count="pageALl">
        </el-pagination>
      </el-row>
    </footer>
    <el-dialog class="addDialog g-twoColumDialog" title="修改信息" :modal="false" :visible.sync="isDialog"
               :before-close="handlerClose">
      <el-form ref="student_info" :model="updateData.student_info" :rules="messageFormRules.student_info"
               label-width="120px"
               v-show="activeForm=='student_info'">
        <el-row :gutter="30">
          <el-col :span="11">
            <el-form-item label="姓名：">
              <el-input v-model="updateData.student_info.name"></el-input>
            </el-form-item>
            <el-form-item label="性别：">
              <el-select v-model="updateData.student_info.sex" style="width:100%;" placeholder="请选择性别">
                <el-option value="女" label="女"></el-option>
                <el-option value="男" label="男"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item class="row" label="出生日期：">
              <el-date-picker type="date" :editable="false" style="width:100%;" placeholder="选择时间"
                              v-model="student_infoTime3"></el-date-picker>
            </el-form-item>
            <el-form-item label="年级：">
              <el-select v-model="updateData.student_info.gradeId" style="width:100%;" placeholder="请选择年级"
                         @change="chooseFormClass" @visible-change="setClassState">
                <el-option v-for="(content,index) in headerButtonData.gradeloadData"
                           :label="content.name"
                           :key="content.gradeid"
                           :value="content.gradeid"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="班级：">
              <el-select v-model="updateData.student_info.classId" style="width:100%;" placeholder="请选择班级" @change="changeNumber" @visible-change="setNumberState">
                <el-option v-for="(content,index) in formClassList"
                           :key='index' :label="content.classname"
                           :value="content.classid"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="班级座号：">
              <el-input v-model="updateData.student_info.serialNumber" :disabled="true"></el-input>
            </el-form-item>
            <el-form-item label="身高：">
              <el-input v-model="updateData.student_info.height"></el-input>
            </el-form-item>
            <el-form-item label="身份证号：">
              <el-input v-model="updateData.student_info.idCard"></el-input>
            </el-form-item>
            <el-form-item label="国籍：">
              <el-input v-model="updateData.student_info.country"></el-input>
            </el-form-item>
            <el-form-item label="民族：">
              <el-select v-model="updateData.student_info.nation" placeholder="请选择民族" style="width: 100%;">
                <el-option :label="national.value" :value="national.value" v-for="(national,ix) in nationalList"
                           :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item class="row" label="入团时间：">
              <el-date-picker type="date" :editable="false" style="width:100%;" placeholder="选择时间"
                              v-model="student_infoTime1"></el-date-picker>
            </el-form-item>
          </el-col>
          <el-col :span="11">
            <el-form-item class="row" label="入学时间：">
              <el-date-picker type="date" :editable="false" style="width:100%;" placeholder="选择时间"
                              v-model="student_infoTime2"></el-date-picker>
            </el-form-item>
            <el-form-item class="row" label="入学方式：">
              <el-select v-model="updateData.student_info.enrolWay" style="width:100%;"
                         placeholder="请选择入学方式">
                <el-option :value="entranceType.value" :label="entranceType.value" v-for="(entranceType,ix) in entranceTypeData" :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item class="row" label="是否住校：">
              <el-radio-group v-model="updateData.student_info.isResSchool">
                <el-radio label="是">是</el-radio>
                <el-radio label="否">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="手机号码：" prop="phone">
              <el-input v-model="updateData.student_info.phone"></el-input>
            </el-form-item>
            <el-form-item label="现住地址：">
              <el-input v-model="updateData.student_info.nowHomePath"></el-input>
            </el-form-item>
            <el-form-item label="现住地址邮编：">
              <el-input v-model="updateData.student_info.nowHomePostcode"></el-input>
            </el-form-item>
            <el-form-item label="政治面貌：">
              <el-select v-model="updateData.student_info.name" placeholder="请选择政治面貌" style="width:100%;">
                <el-option :label="political.value" :value="political.value" v-for="(political,ix) in politicalData" :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="宿舍栋号：">
              <el-input v-model="updateData.student_info.dorStore"></el-input>
            </el-form-item>
            <el-form-item label="宿舍楼层：">
              <el-input v-model="updateData.student_info.dorStorey"></el-input>
            </el-form-item>
            <el-form-item label="宿舍号：">
              <el-input v-model="updateData.student_info.dorNumber" :disabled="true"></el-input>
            </el-form-item>
            <el-form-item label="学生卡号：">
              <el-input v-model="updateData.student_info.studentCard"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-form ref="school_rollinfo" :model="updateData.school_rollinfo" label-width="120px"
               v-show="activeForm=='school_rollinfo'">
        <el-row :gutter="30">
          <el-col :span="11">
            <el-form-item label="是否在校：">
              <el-radio-group v-model="updateData.school_rollinfo.isAtSchool" :disabled="true">
                <el-radio label="是">是</el-radio>
                <el-radio label="否">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否休学：">
              <el-radio-group v-model="updateData.school_rollinfo.isLeave" :disabled="true">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="科类：">
              <el-select v-model="updateData.school_rollinfo.stream" style="width: 100%;" placeholder="请选择科类"
                         @change="chooseMajor" @visible-change="setMajorState">
                <el-option :label="branch.branch" :value="branch.branch" v-for="branch in branchList"
                           :key="branch.branchid"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="国家学号：">
              <el-input v-model="updateData.school_rollinfo.nationalNumber"
                        placeholder="请输入国家学号"></el-input>
            </el-form-item>
            <el-form-item label="省统编学号：">
              <el-input v-model="updateData.school_rollinfo.provinceNumber"
                        placeholder="请输入省统编学号"></el-input>
            </el-form-item>
            <el-form-item label="市统编学号：">
              <el-input v-model="updateData.school_rollinfo.cityNumber"
                        placeholder="请输入市统编学号"></el-input>
            </el-form-item>
            <el-form-item label="录取类别：">
              <el-select v-model="updateData.school_rollinfo.admCategory"
                         style="width: 100%;"
                         placeholder="请选择录取类别">
                <el-option :label="admittedType.value" :value="admittedType.value" v-for="(admittedType,ix) in admittedTypeData" :key="ix"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="11">
            <el-form-item label="是否挂靠：">
              <el-radio-group v-model="updateData.school_rollinfo.isSubor" :disabled="true">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否借读：">
              <el-radio-group v-model="updateData.school_rollinfo.isTempStudy">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="专业：">
              <el-select v-model="updateData.school_rollinfo.major" style="width: 100%;"
                         placeholder="请选择专业">
                <el-option :label="major.majorname" :value="major.majorname" v-for="major in majorList"
                           :key="major.majorid"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="小学学校：">
              <el-input v-model="updateData.school_rollinfo.eleSchool"
                        placeholder="请输入小学学校"></el-input>
            </el-form-item>
            <el-form-item label="中学学校：">
              <el-input v-model="updateData.school_rollinfo.secSchool"
                        placeholder="请输入中学学校"></el-input>
            </el-form-item>
            <el-form-item label="中考分数：">
              <el-input v-model="updateData.school_rollinfo.midExam"
                        placeholder="请输入中考分数"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-form ref="cen_reg_info" :model="updateData.cen_reg_info" label-width="130px"
               v-show="activeForm=='cen_reg_info'">
        <el-row :gutter="30">
          <el-col :span="11">
            <el-form-item label="户口所在地：">
              <el-input v-model="updateData.cen_reg_info.perAddress"
                        placeholder="请输入户口所在地"></el-input>
            </el-form-item>
            <el-form-item label="户籍类型：">
              <el-select v-model="updateData.cen_reg_info.cenRegType" style="width: 100%;"
                         placeholder="请选择户籍类型">
                <el-option :label="tenanciesType.value" :value="tenanciesType.value" v-for="(tenanciesType,ix) in tenanciesTypeData" :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="户口性质：">
              <el-input v-model="updateData.cen_reg_info.cenRegNature"
                        placeholder="请输入户口性质"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="11">
            <el-form-item label="户口所在地代码：">
              <el-input v-model="updateData.cen_reg_info.perAddressCode"
                        placeholder="请输入户口所在地代码"></el-input>
            </el-form-item>
            <el-form-item label="生源地代码：">
              <el-input v-model="updateData.cen_reg_info.originCode"
                        placeholder="请输入生源地代码"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-form ref="parents_info" :model="updateData.parents_info" :rules="messageFormRules.parents_info"
               label-width="180px"
               v-show="activeForm=='parents_info'">
        <el-row :gutter="0">
          <el-col :span="12">
            <el-form-item label="家长1姓名：">
              <el-input v-model="updateData.parents_info.jzName1"
                        placeholder="请输入家长1姓名"></el-input>
            </el-form-item>
            <el-form-item label="家长1联系电话：" prop="jzPhone1">
              <el-input v-model="updateData.parents_info.jzPhone1"
                        placeholder="请输入家长1联系电话"></el-input>
            </el-form-item>
            <el-form-item label="家长1工作单位：">
              <el-input v-model="updateData.parents_info.jzWorkUnit1"
                        placeholder="请输入家长1工作单位"></el-input>
            </el-form-item>
            <el-form-item label="家长1居住地址：">
              <el-input v-model="updateData.parents_info.resAddress1"
                        placeholder="请输入家长1居住地址"></el-input>
            </el-form-item>
            <el-form-item label="家长1身份证类型：">
              <el-select v-model="updateData.parents_info.jzCardType1" style="width:100%;"
                         placeholder="请选择家长1身份证类型">
                <el-option :label="idCardType.value" :value="idCardType.value" v-for="(idCardType,ix) in idCardTypeData" :key="ix"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="家长1关系说明：">
              <el-input v-model="updateData.parents_info.relation1"
                        placeholder="请输入关系"></el-input>
            </el-form-item>
            <el-form-item label="家长1是否监护人：">
              <el-radio-group v-model="updateData.parents_info.isGuardian1">
                <el-radio label="是">是</el-radio>
                <el-radio label="否">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="家长1职务：">
              <el-input v-model="updateData.parents_info.jzPost1"
                        placeholder="请输入家长1职务"></el-input>
            </el-form-item>
            <el-form-item label="家长1民族：">
              <el-select v-model="updateData.parents_info.jzNation1" placeholder="请选择民族" style="width: 100%;">
                <el-option :label="national.value" :value="national.value" v-for="(national,ix) in nationalList"
                           :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="家长1身份证号：">
              <el-input v-model="updateData.parents_info.jzIdCard1"
                        placeholder="请输入家长1身份证号"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="0">
          <el-col :span="12">
            <el-form-item label="家长2姓名：">
              <el-input v-model="updateData.parents_info.jzName2"
                        placeholder="请输入家长2姓名"></el-input>
            </el-form-item>
            <el-form-item label="家长2联系电话：" prop="jzPhone2">
              <el-input v-model="updateData.parents_info.jzPhone2"
                        placeholder="请输入家长2联系电话"></el-input>
            </el-form-item>
            <el-form-item label="家长2工作单位：">
              <el-input v-model="updateData.parents_info.jzWorkUnit2"
                        placeholder="请输入家长2工作单位"></el-input>
            </el-form-item>
            <el-form-item label="家长2居住地址：">
              <el-input v-model="updateData.parents_info.resAddress2"
                        placeholder="请输入家长2居住地址"></el-input>
            </el-form-item>
            <el-form-item label="家长2身份证类型：">
              <el-select v-model="updateData.parents_info.jzCardType2" style="width: 100%;"
                         placeholder="请选择家长2身份证类型">
                <el-option :label="idCardType.value" :value="idCardType.value" v-for="(idCardType,ix) in idCardTypeData" :key="ix"></el-option>
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="家长2关系说明：">
              <el-input v-model="updateData.parents_info.relation2"
                        placeholder="请输入关系"></el-input>
            </el-form-item>
            <el-form-item label="家长2是否监护人：">
              <el-radio-group v-model="updateData.parents_info.isGuardian2">
                <el-radio label="是">是</el-radio>
                <el-radio label="否">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="家长2职务：">
              <el-input v-model="updateData.parents_info.jzPost2"
                        placeholder="请输入家长2职务"></el-input>
            </el-form-item>
            <el-form-item label="家长2民族：">
              <el-select v-model="updateData.parents_info.jzNation2" placeholder="请选择民族" style="width: 100%;">
                <el-option :label="national.value" :value="national.value" v-for="(national,ix) in nationalList"
                           :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="家长2身份证号：">
              <el-input v-model="updateData.parents_info.jzIdCard2"
                        placeholder="请输入家长2身份证号"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-form ref="tuition_info" :model="updateData.tuition_info" label-width="130px"
               v-show="activeForm=='tuition_info'">
        <el-row :gutter="30">
          <el-col :span="11">
            <el-form-item label="开户银行：">
              <el-input v-model="updateData.tuition_info.openBank"
                        placeholder="请输入开户银行"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="11">
            <el-form-item label="开户银行账号：">
              <el-input v-model="updateData.tuition_info.bankAccount"
                        placeholder="请输入开户银行账号"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <el-form ref="other_info" :model="updateData.other_info" :rules="messageFormRules.other_info" label-width="200px"
               v-show="activeForm=='other_info'">
        <el-row :gutter="30">
          <el-col :span="12">
            <el-form-item label="是否独生子女：">
              <el-radio-group v-model="updateData.other_info.isSingleton">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否留守儿童：">
              <el-radio-group v-model="updateData.other_info.isBehChild">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否孤儿：">
              <el-radio-group v-model="updateData.other_info.isOrphan">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否烈士或优抚子女：">
              <el-radio-group v-model="updateData.other_info.isMartyrChild">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否需要申请资助：">
              <el-radio-group v-model="updateData.other_info.isApplyFund">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="残疾人类型：">
              <el-select v-model="updateData.other_info.disPerType" style="width: 100%;"
                         placeholder="请选择残疾人类型">
                <el-option :label="disabledType.value" :value="disabledType.value" v-for="(disabledType,ix) in disabledTypeData" :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="上下学交通距离：">
              <el-input v-model="updateData.other_info.distance"
                        placeholder="请输入上下学交通距离"></el-input>
            </el-form-item>
            <el-form-item label="社工证号：">
              <el-input v-model="updateData.other_info.workerNumber"
                        placeholder="请输入社工证号"></el-input>
            </el-form-item>
            <el-form-item label="通信地址：">
              <el-input v-model="updateData.other_info.mailAddress"
                        placeholder="请输入通信地址"></el-input>
            </el-form-item>
            <el-form-item label="血型：">
              <el-select v-model="updateData.other_info.bloodType" style="width: 100%;"
                         placeholder="请选择血型">
                <el-option :label="bloodType.value" :value="bloodType.value" v-for="(bloodType,ix) in bloodTypeData" :key="ix"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="电子邮箱：" prop="email">
              <el-input v-model="updateData.other_info.email"
                        placeholder="请输入电子邮箱"></el-input>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="是否受过学前教育：">
              <el-radio-group v-model="updateData.other_info.isPreschool">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否进城务工人员随迁子女：">
              <el-radio-group v-model="updateData.other_info.isTraChild">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否需要乘坐校车：">
              <el-radio-group v-model="updateData.other_info.isTakeSchoolBus">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否享受一补：">
              <el-radio-group v-model="updateData.other_info.isSubsidy">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="是否由政府购买学位：">
              <el-radio-group v-model="updateData.other_info.isGovBuyDegree">
                <el-radio label="1">是</el-radio>
                <el-radio label="0">否</el-radio>
              </el-radio-group>
            </el-form-item>
            <el-form-item label="上学交通方式：">
              <el-input v-model="updateData.other_info.trafficMode"
                        placeholder="请输入上学交通方式"></el-input>
            </el-form-item>
            <el-form-item label="健康状况：">
              <el-select v-model="updateData.other_info.healthStatus" style="width:100%;"
                         placeholder="请选择健康状况">
                <el-option value="2" label="差"></el-option>
                <el-option value="1" label="良好"></el-option>
                <el-option value="0" label="健康"></el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="学籍辅号：">
              <el-input v-model="updateData.other_info.auxNumber"
                        placeholder="请输入学籍辅号"></el-input>
            </el-form-item>
            <el-form-item label="学生来源：">
              <el-input v-model="updateData.other_info.studentSource"
                        placeholder="请输入学生来源"></el-input>
            </el-form-item>
            <el-form-item label="主页地址：">
              <el-input v-model="updateData.other_info.businessPage"
                        placeholder="请输入主页地址"></el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button type="primary" @click="updateAjax">确定</el-button>
        <el-button @click="isDialog=false">取消</el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
  import {
    studentMessageGradeLoad, studentMessageClassLoad, studentMessageTypeLoad,
    /*页面加载数据*/
    studentMessageSearchLoad,
    studentMessageDelete,//删除数据
    studentMessageUpdate,//修改数据
    studentMessageExport,//导出数据
  } from '@/api/http'
  import req from '@/assets/js/common'
  import {ethnics,politicalStatus,idCardType,disabledType,bloodType,entranceType,admittedType,tenanciesType} from '@/assets/js/common-const-data'
  import moment from 'moment'

  export default{
    data(){
      var regPhone = /0?(13|14|15|18)[0-9]{9}$/,
        regEmail = /\w+((-w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+/;
      var validatePhone = (rule, value, callback) => {
        if (value && !regPhone.test(value)) {
          callback(new Error('请输入正确手机号'));
        } else {
          callback();
        }
      };
      var validateEmail = (rule, value, callback) => {
        if (value && !regEmail.test(value)) {
          callback(new Error('请输入正确邮箱格式'));
        } else {
          callback();
        }
      };
      return {
        isLoading:false,
        politicalData:politicalStatus,
        idCardTypeData:idCardType,
        nationalList:ethnics,
        disabledTypeData:disabledType,
        bloodTypeData:bloodType,
        entranceTypeData:entranceType,
        admittedTypeData:admittedType,
        tenanciesTypeData:tenanciesType,
        /*form表单规则*/
        studentMsgRules: {
          grade: [{required: true, message: ''}],
          classes: [{required: true, message: ''}],
          messageType: [{required: true, message: ''}],
        },
        /*ajax data*/
        headerButtonData: {
          gradeloadData: [],//年级返回数组
          classesLoadData: [],//班级返回数组
          msgTypeLoadData: [],//信息类别返回数组
          /*下面表格信息数据*/
          hasBasicData: false,
          studentBasicMsg: [],//学生基本信息数据
          studentBasicTr: [],//学生基本信息列
        },
        /*ajax params*/
        ajaxClasses: [],
        ajaxGrade: '',
        /*form表单双向绑定数据*/
        dataHeader: {
          grade: '',
          classes: '',
          messageType: ''
        },
        fuzzyInput: '',
        mutipleChoose: [],
        /*page*/
        pageALl: 1,
        currentPage: 1,
        pageSize: 50,
        /*弹框*/
        dialogTitle: '学生补录',
        isDialog: false,
        activeForm: 'student_info',
        activeChoice: '',
        student_infoTime1: '',
        student_infoTime2: '',
        student_infoTime3: '',
        branchList: [],
        majorList: [],
        majorState: false,
        classState: false,
        serialNumberState:false,
        formClassList: [],
        updateData: {
          /*基本信息*/
          student_info: {
            logo: '',
            gradeId: '',
            classId: '',
            sex: '',
            birthday: '',
            serialNumber: '',
            height: '',
            idCard: '',
            country: '',
            nation: '',
            leagueTime: '',
            enrolTime: '',
            enrolWay: '',
            isResSchool: '',
            phone: '',
            homePath: '',
            homePostcode: '',
            nowHomePath: '',
            nowHomePostcode: '',
            dorStore: '',
            dorStorey: '',
            dorNumber: '',
            studentCard: ''
          },
          /*学籍信息*/
          school_rollinfo: {
            isAtSchool: '是',
            isLeave: '0',
            isSubor: '0',
            isTempStudy: '0',
            stream: '',
            major: '',
            nationalNumber: '',
            provinceNumber: '',
            cityNumber: '',
            admCategory: '',
            eleSchool: '',
            secSchool: '',
            midExam: ''
          },
          /*户籍信息*/
          cen_reg_info: {
            perAddress: '',
            perAddressCode: '',
            cenRegType: '',
            originCode: '',
            cenRegNature: ''
          },
          /*家长信息*/
          parents_info: {
            jzName1: '',
            relation1: '',
            jzPhone1: '',
            resAddress1: '',
            jzWorkUnit1: '',
            jzPost1: '',
            jzCardType1: '',
            jzIdCard1: '',
            jzNation1: '',
            jzName2: '',
            relation2: '',
            jzPhone2: '',
            jzWorkUnit2: '',
            jzPost2: '',
            jzCardType2: '',
            resAddress2: '',
            jzIdCard2: '',
            jzNation2: '',
            isGuardian1: '是',
            isGuardian2: '是'
          },
          /*学费信息*/
          tuition_info: {
            openBank: '',
            bankAccount: ''
          },
          /*其他信息*/
          other_info: {
            disPerType: '',
            trafficMode: '',
            distance: '',
            workerNumber: '',
            healthStatus: '',
            mailAddress: '',
            bloodType: '',
            studentSource: '',
            email: '',
            businessPage: '',
            isSingleton: '0',
            isBehChild: '0',
            isOrphan: '0',
            isMartyrChild: '0',
            isApplyFund: '0',
            isPreschool: '0',
            isTraChild: '0',
            isTakeSchoolBus: '0',
            isSubsidy: '0',
            isGovBuyDegree: '0',
            auxNumber: ''
          }
        },
        messageFormRules: {
          student_info: {
            phone: [
              {validator: validatePhone, trigger: 'blur'}
            ]
          },
          parents_info: {
            jzPhone1: [
              {validator: validatePhone, trigger: 'blur'}
            ],
            jzPhone2: [
              {validator: validatePhone, trigger: 'blur'}
            ]
          },
          other_info: {
            email: [
              {validator: validateEmail, trigger: 'blur'}
            ]
          }
        }
      }
    },
    computed: {},
    created(){
      var self = this;
      self.sendLoadAjax();
      //得到科类
      req.ajaxSend('/school/user/userGl?type=getBranch', 'get', '', function (res) {
        self.branchList = res.data;
      });
    },
    methods: {
      operationData(type){
        if(!this.headerButtonData.studentBasicMsg.length){
          this.vmMsgWarning('暂无数据');
          return;
        }
        let sAy = [], hdData = {};
        this.headerButtonData.studentBasicTr.forEach(val=>{
          hdData[val.en]=val.zh;
        });
        sAy.push(hdData);
        for (let obj of this.headerButtonData.studentBasicMsg) {
          let d = {};
          for (let name in hdData) {
            d[name] = obj[name] || '';
          }
          sAy.push(d);
        }
        if (type === 'copy') {
          req.copyTableData('.StudentInformationInquiry', sAy);
        } else {
          req.lodop(sAy);
        }
      },
      handleCurrentChange(val) {
        this.currentPage = val;
        this.searchStudentAjax({
          page: this.currentPage,
          ListType: this.activeChoice,
          class: this.ajaxClasses,
          grade: this.ajaxGrade,
          pageSize: this.pageSize
        });
      },
      handleStudentTable(section){
        this.mutipleChoose = section;
      },
      sortChange(column){
        this.searchStudentAjax({
          ListType: this.activeChoice,
          sort: column.order||'',
          sortType: column.prop||'',
          class: this.ajaxClasses,
          grade: this.ajaxGrade,
          pageSize: this.pageSize
        });
      },
      buttonClick(event){
        const e = $(event.currentTarget), targetMsg = e.data('msg');
        if (targetMsg == 'delete') {
          this.deleteAjax();
        } else if (targetMsg == 'export') {
          this.exportAjax();
        }
      },
      changeBasicMsg(index){
        /*修改信息弹框*/
        var self = this, data = {
          branchid: ''
        };
        self.isDialog = true;
        self.dialogTitle = '修改信息';
        if (self.activeForm == 'student_info') {
          self.student_infoTime1 = self.headerButtonData.studentBasicMsg[index].leagueTime ? new Date(self.headerButtonData.studentBasicMsg[index].leagueTime) : self.headerButtonData.studentBasicMsg[index].leagueTime;
          self.student_infoTime2 = self.headerButtonData.studentBasicMsg[index].enrolTime ? new Date(self.headerButtonData.studentBasicMsg[index].enrolTime) : self.headerButtonData.studentBasicMsg[index].enrolTime;
          self.student_infoTime3 = self.headerButtonData.studentBasicMsg[index].birthday ? new Date(self.headerButtonData.studentBasicMsg[index].birthday) : self.headerButtonData.studentBasicMsg[index].birthday;
        }
        $.extend(self.updateData[self.activeForm], self.headerButtonData.studentBasicMsg[index]);
        if (self.activeForm == 'school_rollinfo') {
          for (let obj of self.branchList) {
            if (obj.branch == self.updateData[self.activeForm].stream) {
              data.branchid = obj.branchid;
            }
          }
          req.ajaxSend('/school/user/userGl?type=getMajor', 'get', data, function (res) {
            self.majorList = res.data;
          })
        }
        if(self.activeForm=='student_info'){
          studentMessageClassLoad({gradeid: self.updateData[self.activeForm].gradeId}).then(data => {
            if (data) {
              self.formClassList = data;
            } else {
              self.formClassList = [];
            }
          });
        }
      },
      setMajorState(state){
        this.majorState = state;
      },
      chooseMajor(){  //弹框选择专业
        var self = this, data = {
          branchid: ''
        };
        for (let obj of self.branchList) {
          if (obj.branch == self.updateData.school_rollinfo.stream) {
            data.branchid = obj.branchid;
          }
        }
        if (self.majorState) {
          self.updateData.school_rollinfo.major = '';
        }
        req.ajaxSend('/school/user/userGl?type=getMajor', 'get', data, function (res) {
          self.majorList = res.data;
        })
      },
      setClassState(state){
        this.classState = state;
      },
      chooseFormClass(){  //弹框选择班级
        if (this.classState) {
          this.updateData.student_info.classId = '';
          this.updateData.student_info.serialNumber = '';
        }
        studentMessageClassLoad({gradeid: this.updateData.student_info.gradeId}).then(data => {
          if (data) {
            this.formClassList = data;
          } else {
            this.formClassList = [];
          }
        });
      },
      setNumberState(state){
        this.serialNumberState = state;
      },
      changeNumber(){
        if (this.serialNumberState) {
          this.updateData.student_info.serialNumber = '';
        }
      },
      /*模糊查询*/
      fuzzyClick(){
        /*点击search按钮*/
        this.searchStudentAjax({
          ListType: this.activeChoice,
          class: this.ajaxClasses,
          grade: this.ajaxGrade,
          pageSize: this.pageSize,
          value: this.fuzzyInput
        });
      },
      handlerClose(done){
        done();
      },
      /*send ajax*/
      updateAjax(){
        /*userId:this.updateData.userId,*/
        if (this.activeForm == 'student_info') {
          this.updateData[this.activeForm].leagueTime = this.student_infoTime1 ? moment(this.student_infoTime1).format('YYYY-MM-DD') : this.student_infoTime1;
          this.updateData[this.activeForm].enrolTime = this.student_infoTime2 ? moment(this.student_infoTime2).format('YYYY-MM-DD') : this.student_infoTime2;
          this.updateData[this.activeForm].birthday = this.student_infoTime3 ? moment(this.student_infoTime3).format('YYYY-MM-DD') : this.student_infoTime3;
        }
        this.$refs[this.activeForm].validate((valid) => {
          if (valid) {
            studentMessageUpdate({
              ListType: this.activeForm,
              updataData: this.updateData[this.activeForm]
            }).then(data => {
              if (data.statu) {
                this.isDialog = false;
                this.searchClick('studentMessge', 1);
                this.vmMsgSuccess('修改成功!');
              } else {
                this.vmMsgError('修改失败!');
              }
            })
          } else {
            return false;
          }
        })
      },
      deleteAjax(){
        /*this.resetClick('studentMessge');*/
        if (this.mutipleChoose.length > 0) {
          this.$confirm('是否删除学生信息（删除后不能恢复）?', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
          }).then(() => {
            const deleteArr = [];
            this.mutipleChoose.forEach((value) => {
              deleteArr.push(value.userId);
            });
            studentMessageDelete({userId: deleteArr}).then(data => {
              if (data.statu > 0) {
                this.vmMsgSuccess('删除成功!');
                /*刷新页面数据*/
                this.searchStudentAjax({
                  ListType: this.activeChoice,
                  class: this.ajaxClasses,
                  grade: this.ajaxGrade,
                  pageSize: this.pageSize
                });
              } else {
                this.vmMsgError('删除失败!');
              }
            });
          }).catch(() => {
          });
        } else {
          this.vmMsgWarning('请选择删除数据!');
        }
      },
      /*导出数据*/
      exportAjax(){
        this.handlerParams();
        this.$refs['studentMessge'].validate((valid) => {
          if (valid) {
            req.downloadFile('.g-container', '/school/user/exportExcel?type=' + this.activeChoice + '&class=' + this.ajaxClasses + '&grade=' + this.ajaxGrade, 'post');
          } else {
            this.vmMsgWarning('请填完整相关信息!');
          }
        });
      },
      sendLoadAjax(){
        studentMessageGradeLoad().then(data => {
          this.headerButtonData.gradeloadData = data;
        });
        studentMessageTypeLoad().then(data => {
          this.headerButtonData.msgTypeLoadData = data;
        });

      },
      gradeClick(){
        /*获取当前年级的班级*/
        this.dataHeader.classes='';
        this.headerButtonData.classesLoadData = [];
        studentMessageClassLoad({gradeid: this.dataHeader.grade}).then(data => {
          if (data) {
            this.headerButtonData.classesLoadData = data;
          } else {
            this.headerButtonData.classesLoadData = [];
          }
        });
      },
      /*页面数据请求*/
      searchClick(formName, type){
        if (type == 0) {
          this.fuzzyInput = '';
          this.activeChoice = this.dataHeader.messageType;
          switch (this.dataHeader.messageType) {
            case 'jbXi':
              this.activeForm = 'student_info';
              break;
            case 'xjXi':
              this.activeForm = 'school_rollinfo';
              break;
            case 'hjXi':
              this.activeForm = 'cen_reg_info';
              break;
            case 'jzXi':
              this.activeForm = 'parents_info';
              break;
            case 'xfXi':
              this.activeForm = 'tuition_info';
              break;
            case 'qtXi':
              this.activeForm = 'other_info';
              break;
          }
        }
        /*handler params*/
        this.handlerParams();
        /*send ajax*/
        this.searchStudentAjax({
          ListType: this.activeChoice,
          class: this.ajaxClasses,
          grade: this.ajaxGrade,
          pageSize: this.pageSize
        });
      },
      /*send student ajax*/
      searchStudentAjax(params){
        this.isLoading=true;
        this.$refs['studentMessge'].validate((valid) => {
          if (valid) {
            studentMessageSearchLoad(params).then(data => {
              this.headerButtonData.studentBasicMsg = data.data;
              this.headerButtonData.studentBasicTr = data.tr;
              this.pageALl = data.maxpage;
              this.currentPage = Number(data.page);
              this.headerButtonData.hasBasicData = true;
            });
          } else {
            this.vmMsgWarning('请填完整相关信息!');
          }
          this.isLoading=false;
        });
      },
      /*params处理，传grade的name和class的name*/
      handlerParams(){
        /*得到选择班级数组*/
        this.ajaxClasses = [];
        if (this.dataHeader.classes == 'all') {
          this.headerButtonData.classesLoadData.forEach((value) => {
            this.ajaxClasses.push(value.classid);
          });
          /*将this.dataHeader.classes改成包含所有classes的数组*/
        }
        else {
          this.headerButtonData.classesLoadData.forEach((value) => {
            if (value.classid == this.dataHeader.classes) {
              this.ajaxClasses.push(value.classid);
            }
          });
        }
        /*得到当前的年级的name值*/
        this.headerButtonData.gradeloadData.forEach((value) => {
          if (value.gradeid == this.dataHeader.grade) {
            this.ajaxGrade = value.gradeid;
          }
        });
      },
      /*弹框提示*/
      alertDialog(msg, type){
        this.$alert(msg, '提示', {
          confirmButtonText: '确定',
          type: type,
          callback: action => {
          }
        });
      },
    }
  }
</script>
<style lang="less" scoped>
  @import '../../../../style/style';
  @import '../../../../style/userManager/student/studentMessage.less';
  @import '../../../../style/userManager/student/studentManager.css';
</style>


