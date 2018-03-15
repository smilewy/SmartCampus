<template>
  <div class="g-home" style="background: none;-webkit-box-shadow: none;-moz-box-shadow: none;box-shadow: none;">
    <div class="g-homeT">
      <div class="g-homeCalendar">
        <div id="homeCalendar1" style="max-width:74.4%;"></div>
        <div id="homeCalendar2" style="max-width:22.1%;"></div>
      </div>
      <div class="g-homeContact">
        <header class="g-contactHeader">通讯录</header>
        <section>
          <div class="g-contactSearch">
            <img src="../assets/img/commonNav/icon_search.png" />
            <input type="text" @input="fuzzySearch" v-model="fuzzySearchText" placeholder="请输入姓名" />
          </div>
          <div class="g-contactCon">
              <el-collapse accordion>
                <el-collapse-item v-for="item in fuzzySearchResult" :title="item.letter.toUpperCase()" :name="item.letter" :key="item.letter">
                        <ul class="g-contactList">
                            <li class="g-contactRow" v-for="(content,idx) in item.data" :key="idx">
                            <div class="g-contactImg">
                                <img src="../assets/img/commonNav/top_touxiang_no.png" />
                            </div>
                            <div class="g-contactMsg">
                                <p class="s-contactText" v-text="content.name"></p>
                                <p class="s-contactPhone" v-text="content.phone"></p>
                            </div>
                            </li>
                        </ul>
                    </el-collapse-item>
            </el-collapse>
          </div>
        </section>
      </div>
    </div>
    <div class="g-homeB">
      <div class="g-homeNotice">
        <header class="g-liOneRow">
          <h2 class="u-NoticeText">通知公告</h2>
          <router-link to="/newNotice" class="s-NoticeNew">
            <div class="u-NoticeImg">
              <img src="../assets/img/commonNav/home_add.png" />
            </div>
            <div class="g-NoticeText">新建通知</div>
          </router-link>
        </header>
        <section>
          <ul class="m-NoticeRow g-liOneRow" v-for="(content,index) in NoticeData" :key="index" @click="showAlertDetail(content,index)">
            <li class="m-NoticeMsg" v-text="content.title"></li>
            <li class="m-NoticeDate" v-text="content.createTime"></li>
          </ul>
        </section>
      </div>
      <div class="g-homeThing">
        <header>
          <h2 class="u-NoticeText">待办事项</h2>
        </header>
        <section id="ThingPart">
          <div class="m-ThingRow" v-for="(item,idx) in partThings" :key="idx">
            <div class="m-ThingL">
              <h2 class="m-ThingLHeader"># {{item.name}} #</h2>
              <div class="m-ThingLPrompt" v-for="(n,idx) in item.thing" :key="idx" @click="ThingsPart(n,idx,item.name)">
                <span class="m-ThingLPMsg">{{n.title}}</span>
                <span class="m-ThingLPDate" style="margin-left: 1rem">{{n.createTime}}</span>
                <span class="m-ThingLPHour" style="float: right; margin-right: 0">{{n.proposer}}</span>
              </div>
            </div>
          </div>
        </section>
      </div>
      <div class="g-homeExpressway">
        <header>
          <h2 class="u-NoticeText">快速通道</h2>
        </header>
        <section>
          <div class="g-ExpresswayBanner">
            <ul class="j-ExpresswayBanner">
              <li class="j-Page" v-for="(page,ix) in expresswayData" :key="ix">
                <a class="g-channelPart" v-for="(content,idx) in page" :key="idx" tag="div" target="_blank" :href="content.webUrl">
                  <span style="text-align: center" v-text="content.webName"></span>
                </a>
              </li>
            </ul>
          </div>
          <div v-if="expresswayPage>1" class="btn-toggle">
            <p class="prev" @click="togglePage('prev')">
              <i class="el-icon-arrow-left"></i>
            </p>
            <p class="next" @click="togglePage('next')">
              <i class="el-icon-arrow-right"></i>
            </p>
          </div>
        </section>

        <footer>
          <ul>
            <li v-for="n in expresswayPage" :key="n" :data-index="n-1" :style="{background:prevActivePage===n-1?'#ff5b5b':'#c9c9c9'}" @click="changeExpressPage"></li>
          </ul>
        </footer>
      </div>
    </div>
    <div class="g-promptBox">
      <el-dialog class="scheduleDialog" :before-close="handleScheduleClose" :title="scheduleTitle" :modal="false" :visible.sync="isNewScheduleForm">
        <el-form ref="dialogNewForm" :rules="newRules" label-position="right" label-width="85px" :model="newScheduleForm">
          <el-form-item label="标题:" prop="title" required>
            <el-input v-model="newScheduleForm.title"></el-input>
          </el-form-item>
          <el-form-item label="日程类型:" prop="kind" required>
            <el-select v-model="newScheduleForm.kind">
              <el-option label="会议" value="1"></el-option>
              <el-option label="升旗仪式" value="2"></el-option>
              <el-option label="考试" value="3"></el-option>
              <el-option label="集会" value="4"></el-option>
              <el-option label="课程" value="5"></el-option>
              <el-option label="班级活动" value="6"></el-option>
              <el-option label="教职工活动" value="7"></el-option>
            </el-select>
          </el-form-item>
          <el-form-item label="通知范围:" v-if="scheduleType=='add'" prop="scope" required>
            <el-checkbox-group v-model="newScheduleForm.scope">
              <el-checkbox label="自己"></el-checkbox>
              <el-checkbox label="学生"></el-checkbox>
              <el-checkbox label="职工"></el-checkbox>
              <el-checkbox label="教师"></el-checkbox>
              <el-checkbox label="家长"></el-checkbox>
              <!--<el-checkbox :label="val" :key="index" v-for="(val,index) in scopeData">{{val}}</el-checkbox>-->
            </el-checkbox-group>
          </el-form-item>
          <el-form-item label="开始时间:" prop="startTime" required>
            <el-date-picker type="date" :editable="false" :picker-options="startPickerOption" placeholder="选择开始时间" v-model="newScheduleForm.startTime" style="width: 100%;"></el-date-picker>
          </el-form-item>
          <el-form-item label="结束时间:" prop="endTime" required>
            <el-date-picker type="date" :editable="false" :picker-options="endPickerOption" placeholder="选择结束时间" v-model="newScheduleForm.endTime" style="width: 100%;"></el-date-picker>
          </el-form-item>
          <el-form-item label="内容:" prop="content" required>
            <el-input type="textarea" v-model="newScheduleForm.content"></el-input>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button class="scheduleConfirm" type="primary" @click="newScheduleConfirm">确 定</el-button>
          <el-button class="scheduleCancel" v-if="scheduleType=='edit'" @click="deleteSchedule">删除</el-button>
        </div>
      </el-dialog>
      <el-dialog
        title="查看通知"
        :visible.sync="dialogVisible"
        :before-close="handleNoticeClose"
        :modal="false" class="alertDetail">
        <h3 class="alertDetail_title">{{actData.title}}</h3>
        <el-row class="alertDetail_subTitle" :gutter="10">
          <el-col :span="5">发布者：{{actData.creator}}</el-col>
          <el-col :span="7" class="alertDetail_subTitle_part">发布部门：{{actData.department}}</el-col>
          <el-col :span="12" class="alertDetail_subTitle_time">发布时间：{{actData.createTime}}</el-col>
        </el-row>
        <el-row class="alertDetail_center">
          <div v-html="actData.content"></div>
        </el-row>
        <el-row>
          <el-button class="alertDetail_annex" type="primary"  @click="download(actData.id)">附件下载</el-button>
        </el-row>
        <el-row class="alertDetail_list clear_fix">
          <div v-for="(actImg,ix) in actData.accessoryName" :key="ix">
            <img src="../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_word.png" alt="" v-if="actImg.split('.')[1]=='doc'||actImg.split('.')[1]=='docx'">
            <img src="../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_ppt.png" alt="" v-if="actImg.split('.')[1]=='ppt'">
            <img src="../assets/img/schManagementSystem/campusOffice/notificationNotice/icon_excel.png" alt="" v-if="actImg.split('.')[1]=='xlsx'||actImg.split('.')[1]=='xlsm'">
            <span :title="actImg">{{actImg}}</span>
          </div>
        </el-row>
      </el-dialog>
    </div>
    <el-dialog title="待办详情(教师请假)" :visible.sync="teacherLeave"  :modal="false">
      <el-row>
        <el-col :span="24" style="text-align: center;height: 40rem;overflow-y: auto">
          <div class="LeaveRecord-table">
            <div class="LeaveRecord-dialog-title">#{{dialogData.title}}#</div>
            <el-col :span="24">
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">申请人</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.proposer}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">请假类型</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.name}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">开始时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.startTime}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">结束时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.endTime}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">审批过期时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.exceed}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">请假时长</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.duration}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">请假原因</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.reason}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div LeaveRecord-table-div-final">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">创建时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.createTime}}</div>
                </el-col>
              </div>
            </el-col>
            <el-col :span="24">
              <div class="LeaveRecord-state-btn">审批状态</div>
            </el-col>
            <el-col :span="18" :offset="2" style="margin-top: 1.8rem">
              <el-col :span="24" style="padding-bottom: 1.25rem;">
                <el-col :span="5">
                  <span>审批结果：</span>
                </el-col>
                <el-col :span="12" style="text-align: left;font-size: 0;">
                  <span v-for="(list,index) in textLists"
                        :key="index"
                        :class="{ active:changecolor == index}" @click="toggleClass(index,list)"
                        class="LeaveRecord-agreed">{{list.text}}</span>
                </el-col>
              </el-col>
              <el-col :span="24" style="padding-bottom: 1.25rem;">
                <el-col :span="5">
                  <span>审批意见：</span>
                </el-col>
                <el-col :span="12" style="text-align: left">
                  <textarea v-model="dialogData.passReasonText" style="resize:none;border-radius: .5rem;width: 100%"
                            rows="6"></textarea>
                </el-col>
              </el-col>
              <el-col :span="12" :offset="5" style="padding-bottom: 1.25rem;">
                <el-select v-model="dialogData.passReason" placeholder="常用审批意见" style="width: 100%">
                  <el-option
                    v-for="item in options"
                    :key="item.value"
                    :value="item.value">
                  </el-option>
                </el-select>
              </el-col>
              <el-col :span="12" :offset="5" style="padding-bottom: 1.25rem;">
                <el-button type="primary" @click="Teacherconfirm()" class="LeaveRecord-search">确认</el-button>
              </el-col>
            </el-col>
          </div>
        </el-col>
      </el-row>
    </el-dialog>
    <el-dialog title="待办详情(用车申请）" :modal="false" :visible.sync="CarApply">
      <el-row>
        <el-col :span="24" style="text-align: center;height:36rem;overflow-y:auto;">
          <div class="LeaveRecord-table">
            <div class="LeaveRecord-dialog-title">#{{dialogData.title}}#</div>
            <el-col :span="24">
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">用车类型</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.carType}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">申请人</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.proposer}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">用车人数</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.users}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">目的地</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.destination}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">用车联系人</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.contactMan}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">用车联系电话</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.telephone}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">起始时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.startTime}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">结束时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.endTime}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">审批过期时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.exceed}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">用车说明</div>
                </el-col>
                <el-col :span="17">
                  <div  v-html="dialogData.reason"></div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div LeaveRecord-table-div-final">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">创建时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.createTime}}</div>
                </el-col>
              </div>
            </el-col>
            <el-col :span="24">
              <div class="LeaveRecord-state-btn">审批状态</div>
            </el-col>
            <el-col :span="18" :offset="2" style="margin-top: 1.8rem">
              <el-col :span="24" style="padding-bottom: 1.25rem;">
                <el-col :span="5">
                  <span>审批结果：</span>
                </el-col>
                <el-col :span="12" style="text-align: left;font-size: 0;">
                  <span v-for="(list,index) in textLists"
                        :key="index"
                        :class="{ active:changecolor == index}" @click="toggleClass(index,list)"
                        class="LeaveRecord-agreed">{{list.text}}</span>
                </el-col>
              </el-col>
              <el-col :span="24" style="padding-bottom: 1.25rem;">
                <el-col :span="5">
                  <span>审批意见：</span>
                </el-col>
                <el-col :span="12" style="text-align: left">
                  <textarea v-model="dialogData.passReasonText" style="resize:none;border-radius: .5rem;width: 100%" rows="6"></textarea>
                </el-col>
              </el-col>
              <el-col :span="12" :offset="5" style="padding-bottom: 1.25rem;">
                <el-select v-model="dialogData.passReason" placeholder="常用审批意见" style="width: 100%">
                  <el-option
                    v-for="item in options"
                    :key="item.value"
                    :value="item.value">
                  </el-option>
                </el-select>
              </el-col>
              <el-col :span="12" :offset="5" style="padding-bottom: 1.25rem;">
                <el-button type="primary" @click="Carconfirm()" class="LeaveRecord-search">保存</el-button>
              </el-col>
            </el-col>
          </div>
        </el-col>
      </el-row>
    </el-dialog>
    <el-dialog title="待办详情(场地申请)" :modal="false" :visible.sync="FiledApply">
      <el-row>
        <el-col :span="24" style="text-align: center;height:36rem;overflow-y:auto;">
          <div class="LeaveRecord-table">
            <div class="LeaveRecord-dialog-title">#{{dialogData.title}}#</div>
            <el-col :span="24">
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">场地申请类型</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.name}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">活动负责人</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.principal}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">联系方式</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.telephone}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">申请人</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.proposer}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">使用场地</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.duration}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">详细地址</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.address}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">审批过期时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.exceed}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">使用日期</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.occupyTime}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">配置选择</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.outfit}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div LeaveRecord-table-div-final" >
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">说明</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.explain}}</div>
                </el-col>
              </div>
            </el-col>
            <el-col :span="24">
              <div class="LeaveRecord-state-btn">我的审批</div>
            </el-col>
            <el-col :span="18" :offset="2" style="margin-top: 1.8rem">
              <el-col :span="24" style="padding-bottom: 1.25rem;">
                <el-col :span="5">
                  <span>审批结果：</span>
                </el-col>
                <el-col :span="12" style="text-align: left;font-size: 0;">
                  <span v-for="(list,index) in textLists"
                        :key="index"
                        :class="{ active:changecolor == index}" @click="toggleClass(index,list)"
                        class="LeaveRecord-agreed">{{list.text}}</span>
                </el-col>
              </el-col>
              <el-col :span="24" style="padding-bottom: 1.25rem;">
                <el-col :span="5">
                  <span>审批意见：</span>
                </el-col>
                <el-col :span="12" style="text-align: left">
                  <textarea v-model="dialogData.passReasonText" style="resize:none;border-radius: .5rem;width: 100%" rows="6"></textarea>
                </el-col>
              </el-col>
              <el-col :span="12" :offset="5" style="padding-bottom: 1.25rem;">
                <el-select v-model="dialogData.passReason" placeholder="常用审批意见" style="width: 100%">
                  <el-option
                    v-for="item in options"
                    :key="item.value"
                    :value="item.value">
                  </el-option>
                </el-select>
              </el-col>
              <el-col :span="12" :offset="5" style="padding-bottom: 1.25rem;">
                <el-button type="primary" @click="Filedconfirm()" class="LeaveRecord-search">保存</el-button>
              </el-col>
            </el-col>
          </div>
        </el-col>
      </el-row>
    </el-dialog>
    <el-dialog title="待办详情（公文流转）" :modal="false" :visible.sync="Document">
      <el-row>
        <el-col :span="24" style="text-align: center;height:40rem;overflow-y: auto;">
          <div class="LeaveRecord-table">
            <div class="LeaveRecord-dialog-title">#{{dialogData.title}}#</div>
            <el-col :span="24">
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">申请人</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.proposer}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1" >类型</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.name}}</div>
                </el-col>
              </div>
              <!--<div class="LeaveRecord-table-div" style="height: 8rem;">-->
              <!--<el-col :span="7">-->
              <!--<div class="LeaveRecord-table-div-1" style="height: 8rem;line-height: 8rem;">附件</div>-->
              <!--</el-col>-->
              <!--<el-col :span="17">-->
              <!--<div style="text-align: left" v-for="(name,index) in dialogData.accessoryName">-->
              <!--<span style="cursor: pointer;color:#48b6c4;border-bottom: 1px solid #48b6c4;" @click="downloadacc(index)">{{name}} 、</span>-->
              <!--</div>-->
              <!--</el-col>-->
              <!--</div>-->
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">内容</div>
                </el-col>
                <el-col :span="17">
                  <div v-html="dialogData.content"></div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">审批过期时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.exceed}}</div>
                </el-col>
              </div>
              <div class="LeaveRecord-table-div LeaveRecord-table-div-final">
                <el-col :span="7">
                  <div class="LeaveRecord-table-div-1">创建时间</div>
                </el-col>
                <el-col :span="17">
                  <div>{{dialogData.createTime}}</div>
                </el-col>
              </div>
              <el-col :span="24" style="text-align: left;margin: 1rem 0;">
              <span v-for="(list,index) in textList"
                    :key="index"
                    :class="{ ApprovelActive:color == index}" class="otherApprove" @click="toggleColor(index,list)">{{list.text}}</span>
              </el-col>
              <el-col v-if="color===0" :span="18" :offset="2" style="margin-top: 1.8rem">
                <el-col :span="24" style="padding-bottom: 1.25rem">
                  <el-col :span="5">
                    <span>审批环节：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <el-select v-model="link" @change="changeApp()">
                      <el-option
                        v-for="item in AppData"
                        :key="item.name"
                        :value="item.name">
                      </el-option>
                    </el-select>
                  </el-col>
                </el-col>
                <el-col :span="24" style="padding-bottom: 1.25rem">
                  <el-col :span="5">
                    <span>审批人：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <el-select v-model="person" @change="chooseApproval()">
                      <el-option
                        v-for="item in AppDataChild"
                        :key="item.approver"
                        :value="item.approver">
                      </el-option>
                    </el-select>
                  </el-col>
                </el-col>
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="5">
                    <span>审批结果：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <span v-if="person">{{AppDataChild[approvalIndex].result | getResultState}}</span>
                    <span v-else>无</span>
                  </el-col>
                </el-col>
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="5">
                    <span>审批意见：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <span v-if="person">{{AppDataChild[approvalIndex].opinion||'无'}}</span>
                    <span v-else>无</span>
                  </el-col>
                </el-col>
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="5">
                    <span>审批时间：</span>
                  </el-col>
                  <el-col :span="12" style="text-align: left">
                    <span v-if="person">{{AppDataChild[approvalIndex].approveTime||'无'}}</span>
                    <span v-else>无</span>
                  </el-col>
                </el-col>
              </el-col>
              <el-col :span="24" v-if="color===1&&dialogData.name==='通用公文流转'">
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="5" style="text-align: left">
                    <span>审批结果：</span>
                  </el-col>
                  <el-col :span="14" style="text-align: left;font-size: 0;">
                  <span v-for="(list,index) in textLists1"
                        :key="index"
                        :class="{ active:changecolor == index}" @click="toggleClass(index,list)"
                        class="LeaveRecord-agreed">{{list.text}}</span>
                  </el-col>
                </el-col>
                <el-col :span="16" style="text-align: left;margin-left: 6rem">
                  提示：如不选择下一审批人，则表示审批流程结束
                </el-col>
                <el-col :span="24">
                  <el-col :span="24">
                    <el-col :span="13">
                      <el-col :span="24" style="margin-top: 2rem;">
                        <el-col :span="7" style="margin-top: .6rem">下一审批人：</el-col>
                        <el-col :span="17">
                          <el-input v-model="pass.name" placeholder="请从右侧列表中选择"></el-input>
                        </el-col>
                      </el-col>
                      <el-col :span="24" style="margin-top: 2rem;">
                        <el-col :span="7" style="margin-top: .6rem">审批意见：</el-col>
                        <el-col :span="17">
                          <!--<el-input placeholder="请填写审批意见（选填）"-->
                          <!--type="textarea"-->
                          <!--:rows="10"-->
                          <!--v-model="pass.opinion"></el-input>-->
                          <textarea class="textarea" type="text" v-model="pass.opinion" placeholder="请填写审批意见（选填）"></textarea>
                        </el-col>
                      </el-col>
                    </el-col>
                    <el-col :span="9" :offset="1" class="Appset-right">
                      <div class="Appset-right-title">待选人员（单选）</div>
                      <div class="Appset-right-border"></div>
                      <el-col :span="24">
                        <el-tree
                          class="filter-tree"
                          :data="approveData"
                          :props="defaultProps"
                          default-expand-all
                          @node-click="handleNodeClick"
                          ref="tree2">
                        </el-tree>
                      </el-col>
                    </el-col>
                  </el-col>
                  <el-col :span="24" style="margin: 3rem 0 2rem">
                    <el-col :span="2" :offset="11">
                      <el-button type="primary" @click="submitForward()" class="submit">提交</el-button>
                    </el-col>
                  </el-col>
                </el-col>
              </el-col>
              <el-col :span="24" v-if="color===1&&dialogData.name!=='通用公文流转'">
                <el-col :span="24" style="padding-bottom: 1.25rem;">
                  <el-col :span="6" style="">
                    <span>审批结果：</span>
                  </el-col>
                  <el-col :span="14" style="text-align:left;font-size: 0;">
                  <span v-for="(list,index) in textLists0"
                        :key="index"
                        :class="{ active:changecolor == index}" @click="toggleClass(index,list)"
                        class="LeaveRecord-agreed">{{list.text}}</span>
                  </el-col>
                </el-col>
                <el-col :span="24">
                  <el-col :span="22">
                    <!--<el-col :span="24" style="margin-top: 2rem;">-->
                    <!--<el-col :span="7" style="margin-top: .6rem">下一审批人：</el-col>-->
                    <!--<el-col :span="17">-->
                    <!--<el-input v-model="nextApprove"></el-input>-->
                    <!--</el-col>-->
                    <!--</el-col>-->
                    <el-col :span="24" style="margin-top: 2rem;">
                      <el-col :span="7" style="margin-top: .6rem">审批意见：</el-col>
                      <el-col :span="17">
                        <el-input placeholder="请填写审批意见（选填）"
                                  type="textarea"
                                  :rows="10"
                                  v-model="pass.opinion"></el-input>
                      </el-col>
                    </el-col>
                  </el-col>
                  <el-col :span="24" style="margin: 3rem 0 2rem">
                    <el-col :span="2" :offset="11">
                      <el-button type="primary" @click="notDoc()" class="submit">提交</el-button>
                    </el-col>
                  </el-col>
                </el-col>
              </el-col>
            </el-col>
          </div>
        </el-col>
      </el-row>
    </el-dialog>
  </div>
</template>
<script>
  import '@/../static/fullcalendar/fullcalendar.css';
  import formatdata from '@/assets/js/date';
  import '../../static/fullcalendar/moment.min'
  import '../../static/fullcalendar/fullcalendar.min.js'
  import {fuzzyQuery} from '../assets/js/fuzzyQuery'
  import req from '../assets/js/common'
  import moment from 'moment'
  import {
    scheduleHandle,//日常安排
    homeContact,//通讯录
    HomeChannel,//快速通道
    HomeNotice,//通知公告
  } from '@/api/http'
  export default{
    data(){
      let _self=this;
      return {
        //待办事项
        dialogData:{},
        partThings:[],
        changecolor: 0,
        color:1,
        modelType:'',
        teacherLeave:false,
        CarApply:false,
        FiledApply:false,
        Document:false,
        type: '',
        opinion: '',
        id: '',
        AppData:[],
        AppDataChild:[],
        accessory:[],
        accessoryName:[],
        approvalIndex:0,
        link:'',
        person:'',
        nextApprove:'',
        textLists: [{
          text: '同意'
        }, {
          text: '不同意'
        }],
        textLists0:[
          {
            text: '同意'
          }, {
            text: '不同意'
          }
        ],
        textLists1:[
          {
            text: '同意'
          }, {
            text: '不同意'
          },{
            text:'转发'
          }
        ],
        textList:[
          {
            text: '其他审批'
          }, {
            text: '我的审批'
          }
        ],
        options: [{
          value: '情况属实，申请通过'
        }, {
          value: '情况存在异议，申请不予通过'
        }, {
          value: '申请内容不符，申请不予通过'
        }],
        approveData:[],
        defaultProps: {
          children: 'children',
          label: 'label'
        },
        pass:{
          name:'',
          next:{},
          opinion:''
        },
        isNewScheduleForm:false,
        /*通讯录*/
        fuzzySearchData:[],
        fuzzySearchText:'',
        fuzzySearchArr:[],
        /*快速通道*/
        /*数据*/
        expresswayData:[],
        /*footer*/
        expresswayPage:1,//总页数
        prevActivePage:0,
        // expresswayFooter:[],
        homeCalendar2_initDate:new Date(),
        /*通知公告*/
        dialogVisible:false,
        actData:{},
        NoticeData:[],
        /*日程安排*/
        scheduleEvents:[],
        /*新建和编辑日程安排*/
        scheduleTitle:'',
        /*编辑日程刷新日程的参数*/
        updateEvent:null,
        startPickerOption:{
          disabledDate(time){
            if(_self.newScheduleForm.endTime){
              return time.getTime()>_self.newScheduleForm.endTime;
            }
          }
        },
        endPickerOption:{
          disabledDate(time){
            if(_self.newScheduleForm.startTime){
              return time.getTime()<_self.newScheduleForm.startTime;
            }
          }
        },
        newScheduleForm:{
          title:'',
          kind:'',
          startTime:'',//年月日
          endTime:'',
          scope:[],//范围
          content:'',
          id:'',
        },
        scopeData:[],
        newRules:{
          title:[{required:true,message:'请输入标题',trigger:'blur'}],
          kind:[{required:true,message:'请选择日程类型',trigger:'blur'}],
          startTime:[{type:'date',required:true,message:'请选择开始时间',trigger:'blur'}],
          endTime:[{type:'date',required:true,message:'请选择结束时间',trigger:'blur'}],
          scope:[{type:'array',required:true,message:'请选择通知范围',trigger:'blur'}],
          content:[{required:true,message:'请输入内容',trigger:'blur'}],
        },
        /*日程*/
        scheduleType:'add',
      }
    },
    computed:{
      fuzzySearchResult(){
          let filter = this.fuzzySearchArr.filter((val)=>{
              return val.name.indexOf(this.fuzzySearchText)>-1
          });
          return this.pySegSort( filter );
      }
    },
    methods:{
      //待办事项
      BacklogList(){
        req.ajaxSend('/school/Home/task','post',{},(res)=>{
          this.partThings=res.data;
        });
      },
      getApproveList(){
        function suit2tree(data){
          var tree = [],
            final = [];
          if(isArray(data)){
            tree = data;
          }else{
            tree = obj2arr(data);
          }
          tree.forEach(function(val){
            var object = {};
            val.label = val.name || val.custom_id;
            if(val.id || val.custom_id)
              object.id = val.id || val.custom_id;
            if(val.custom_id)
              delete val.custom_id;
            if(val.name)
              delete val.name;
            val.children = deepCopy(val);
            if(val.label&&val.id){
              delete val.children;
            }else{
              val.children = suit2tree(val.children);
              object.children = val.children;
            }
            object.label = val.label;
            if(object.label&&object.label!=='children')
              final.push(object);
          });
          return final;
        }
        function obj2arr(data,key_name){
          if(!isArray(data)){
            var arr = [];
            for(var key in data){
              var object = data[key];
              if(typeof object === 'object'){
                object[key_name||'custom_id'] = key;
                arr.push(object);
              }
            }
            return arr;
          }else{
            return data;
          }
        }
        function isArray(data){
          return data&&!!data.join;
        }
        function deepCopy(data){
          return JSON.parse(JSON.stringify(data));
        }
        let param={
          isCommon:'Y',
        };
        req.ajaxSend('/school/WorkDemand/userLists','post',param,(res)=>{
          this.approveData=suit2tree(res.data);
        });
      },
      downloadacc(idx){
        req.downloadFile('.g-home','/school/WorkDemand/LogDoc?type=download&acc='
          +this.accessory[idx]+'&aNa='+this.accessoryName[idx],'post');
      },
      ThingsPart(data,idx,model){
        this.modelType=model;
        this.changecolor=0;
        this.color=1;
        this.dialogData=data;
        this.nextApprove=data.nextApprove;
        if(this.modelType==="教师请假"){
          this.teacherLeave =true;
          this.$set(data,'passReason','');
          this.$set(data,'passReasonText','');
        }else if(this.modelType==='用车申请'){
          this.CarApply=true;
          this.$set(data,'passReason','');
          this.$set(data,'passReasonText','');
        }
        else if(this.modelType==='场地申请'){
          if(data.occupyTime){
            data.occupyTime=data.occupyTime.join('、');
          }
          this.$set(data,'passReason','');
          this.$set(data,'passReasonText','');
          this.FiledApply=true;
        }else if(this.modelType==='公文流转'){
          this.getApproveList();
          this.link = '';
          this.person = '';
          this.Document=true;
        }
      },
      //待办事项，教师请假审批
      Teacherconfirm() {
        if (!(this.dialogData.passReasonText || this.dialogData.passReason)) {
          this.vmMsgWarning( '请先填写审批意见！' );
          return;
        }
        this.id = this.dialogData.id;
        this.type = this.changecolor===0 ? 'pass' : 'reject';
        this.opinion = this.dialogData.passReasonText === '' ? this.dialogData.passReason : this.dialogData.passReasonText;
        let param = {
          whether: 1,
          type: this.type,
          opinion: this.opinion,
          id: this.id
        };
        this.$confirm('是否确定保留该审批结果?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/TeacherLeave/approve', 'post', param, (res) => {
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
            }else {
              this.vmMsgError( res.msg );
            }
            this.teacherLeave = false;
            this.BacklogList();
          });
        }).catch(() => {

        });
      },
      //待办事项，用车申请审批
      Carconfirm(){
        if(!(this.dialogData.passReasonText || this.dialogData.passReason)){
          this.vmMsgWarning( '请先填写审批意见' );
          return;
        }
        this.id=this.dialogData.id;
        this.type = this.changecolor===0 ? 'pass':'reject';
        this.opinion = this.dialogData.passReasonText===''? this.dialogData.passReason:this.dialogData.passReasonText;
        let param = {
          whether:1,
          type:this.type,
          opinion:this.opinion,
          carId:this.id
        };
        this.$confirm('是否保留该审批结果?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approveCar','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
            }else {
              this.vmMsgError( res.msg );
            }
            this.CarApply = false;
            this.BacklogList();
          });
        }).catch(() => {

        });
      },
      //待办事项，场地申请审批
      Filedconfirm(){
        if(this.dialogData.passReasonText==='' && this.dialogData.passReason===''){
          this.vmMsgWarning( '请先填写审批意见' );
          return;
        }
        this.id=this.dialogData.id;
        this.type = this.changecolor===0 ? 'pass':'reject';
        this.opinion = this.dialogData.passReasonText===''? this.dialogData.passReason:this.dialogData.passReasonText;
        let param = {
          whether: 1,
          type:this.type,
          opinion:this.opinion,
          placeId:this.id
        };
        this.$confirm('是否确定保留该审批结果?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approvePlace','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
            }else {
              this.vmMsgError( res.msg );
            }
            this.FiledApply = false;
            this.BacklogList();
          });
        }).catch(() => {

        });
      },
      submitForward(){
        let param={
          whether:1,
          type:this.changecolor===0 ? 'pass':this.dialogData.pass===false?'reject':'transmit',
          docId:this.dialogData.id,
          isCommon:'Y',
          next:this.pass.next,
          opinion:this.pass.opinion
        };
        this.$confirm('是否确定保留该审批意见?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approveDoc','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
            }else {
              this.vmMsgError( res.msg );
            }
            this.Document=false;
            this.BacklogList();
          });
        }).catch(() => {

        });
      },
      notDoc(){
        let param={
          whether:1,
          type:this.changecolor===0 ? 'pass':'reject',
          isCommon:'N',
          docId:this.dialogData.id,
          opinion:this.pass.opinion
        };
        this.$confirm('是否确定保留该审批意见?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          req.ajaxSend('/school/WorkDemand/approveDoc','post',param,(res)=>{
            if(res.status===1){
              this.vmMsgSuccess( res.msg );
            }else {
              this.vmMsgError( res.msg );
            }
            this.BacklogList();
            this.Document=false;
          });
        }).catch(() => {

        });
      },
      toggleClass(index, list){
        this.changecolor = index;
      },
      toggleColor(index,list){
        this.color = index;
        this.link='';
        this.person='';
        let param={
          type:'detail',
          id:this.dialogData.id
        };
        req.ajaxSend('/school/WorkDemand/LogDoc','post',param,(res)=>{
          res.data.forEach(val=>{
            this.accessory=val.accessory;
            this.accessoryName=val.accessoryName;
            this.dialogData=val;
            this.AppData=val.process;
          });
        });
      },
      chooseApproval(){
        this.AppDataChild.forEach((val, idx) => {
          if (val.approver === this.person) {
            this.approvalIndex = idx;
          }
        })
      },
      changeApp(){
        this.AppData.forEach(val => {
          if (val.name === this.link) {
            this.AppDataChild = val.child;
            this.person=val.child[0].approver;
          }
        })
      },
      handleNodeClick(data){
        if(data.children)return;
        this.pass.name=data.label;
        this.pass.next={
          id:data.id,
          name:data.label
        }
      },
      togglePage(type){
        let len = this.expresswayPage,
          activeIndex = this.prevActivePage;
        if(type==='prev'){
          if(activeIndex===0){
            activeIndex = len-1
          }else{
            activeIndex--;
          }
          this.showActivePage(activeIndex);
        }else if(type==='next'){
          if(activeIndex===len-1){
            activeIndex = 0;
          }else{
            activeIndex++;
          }
          this.showActivePage(activeIndex);
        }
      },
      changeExpressPage(event){
        const e=$(event.currentTarget),
        domIndex = Number(e.attr('data-index'));
        if(domIndex===this.prevActivePage){ return false; }
        this.showActivePage(Number(e.attr('data-index')));
      },
      showActivePage(activePage){
        $('li.j-Page').eq(activePage).animate({zIndex:'2',left:'0rem'},300);
        $('li.j-Page').eq(this.prevActivePage).animate({zIndex:'1',left:'-16.5rem'},300).animate({zIndex:'1',left:'16.5rem'},0);
        this.prevActivePage=activePage;
      },
      /*通知公告*/
      handleNoticeClose(done){
        done();
      },
      download(id){
        req.downloadFile('.g-home','/school/Home/lists?download=ensure&nid='+id,'post');
      },
      showAlertDetail(data, idx){
        var toData = {
          type: 'check',
          nid: data.id
        };
        let self = this;
        self.actData = data;
        self.dialogVisible = true;
        if (self.NoticeData[idx].status == '未查阅') {
          req.ajaxSend('/school/Notification/lists', 'post', toData, function (res) {
            if (res.status == 1) {
              self.NoticeData[idx].status = '已查阅';
            }
          })
        }
      },

      /*新建日程click*/
      showNewSchedule(){
        this.isNewScheduleForm=true;
        this.scheduleTitle='新增日程';
        this.scheduleType='add';
        this.newScheduleForm={title:'',kind:'',startTime:'',endTime:'',scope:[],content:'',id:''};
      },
      /*编辑日程*/
      showHandlerSchedule(eventObj){
        this.isNewScheduleForm=true;
        this.scheduleTitle='编辑日程';
        this.scheduleType='edit';
        this.newScheduleForm.id=eventObj._id;
        this.newScheduleForm.kind=eventObj.kind;
        this.newScheduleForm.title=eventObj.title;
        this.newScheduleForm.startTime=eventObj.start._d;
        this.newScheduleForm.endTime=eventObj.end?eventObj.end._d:new Date(eventObj.endStr);
        this.newScheduleForm.content=eventObj.description;
      },
      fuzzySearch(){
      },
      handleScheduleClose(done){
        done();
      },
      /*send ajax---------------*/
      scheduleLoadAjax(){
        scheduleHandle().then(data=>{
          if(data.status){
            data.data.forEach((val,idx)=>{
              val.endStr=val.end;
              $('#homeCalendar1').fullCalendar('renderEvent',val,true);
              $('#homeCalendar2').fullCalendar('renderEvent',val,true);
            });
            this.scheduleEvents=data.data;
          }
          else{
            this.scheduleEvents=[];
          }
        })
      },
      /*新建及编辑日程*/
      newScheduleConfirm(){
        this.$refs.dialogNewForm.validate((valid)=>{
          if(valid){
            this.isNewScheduleForm =false;
            this.newScheduleForm.startTime=moment(this.newScheduleForm.startTime).format('YYYY-MM-DD HH:mm:ss');
            this.newScheduleForm.endTime=moment(this.newScheduleForm.endTime).format('YYYY-MM-DD HH:mm:ss');
            scheduleHandle({...this.newScheduleForm,type:this.scheduleType}).then(data=>{
              if(this.scheduleType=='add'){
                /*添加日程*/
                if(data.status){
                  $('#homeCalendar1').fullCalendar('renderEvent',{id:data.id,kind:this.newScheduleForm.kind,title:this.newScheduleForm.title,start:this.newScheduleForm.startTime,end:this.newScheduleForm.endTime,description:this.newScheduleForm.content},true);
                  $('#homeCalendar2').fullCalendar('renderEvent',{id:data.id,kind:this.newScheduleForm.kind,title:this.newScheduleForm.title,start:this.newScheduleForm.startTime,end:this.newScheduleForm.endTime,description:this.newScheduleForm.content},true);
                  this.vmMsgSuccess( '新增日程成功！' );
                }
                else{
                  this.vmMsgError( '新增日程失败！' );
                }
              }
              else{
                /*编辑日程*/
                if(data.status){
                  /*用updateEvent事件*/
                  this.updateEvent.id=this.newScheduleForm.id;
                  this.updateEvent.kind=this.newScheduleForm.kind;
                  this.updateEvent.title=this.newScheduleForm.title;
                  this.updateEvent.start=this.newScheduleForm.startTime;
                  this.updateEvent.end=this.newScheduleForm.endTime;
                  this.updateEvent.description=this.newScheduleForm.content;
                  $('#homeCalendar1').fullCalendar('updateEvent',this.updateEvent);
                  $('#homeCalendar2').fullCalendar('updateEvent',this.updateEvent);
                  this.vmMsgSuccess( '编辑日程成功！' );
                }
                else{
                  this.vmMsgError( '编辑日程失败！' );
                }
              }
            });
          }
        });
      },
      /*删除日程*/
      deleteSchedule(){
        this.$confirm('确定删除该日程？','提示',{
          confirmButtonText:'确定',
          cancelButtonText:'取消',
          type:'warning'
        }).then(()=>{
          this.isNewScheduleForm =false;
          scheduleHandle({id:this.newScheduleForm.id,type:'del'}).then(data=>{
            /*编辑日程*/
            if(data.status==1){
              /*用removeEvents事件*/
              $('#homeCalendar1').fullCalendar('removeEvents',this.updateEvent.id);
              $('#homeCalendar2').fullCalendar('removeEvents',this.updateEvent.id);
              this.vmMsgSuccess( data.msg );
            }else if(data.status==2){
              this.vmMsgWarning( '只有发布者或者管理员可以删除' );
            }else{
              this.vmMsgError( data.msg );
            }
          });
        }).catch(()=>{});
      },
      pySegSort( arr, empty ){
          if(!String.prototype.localeCompare)
            return null;

            const letters ="*abcdefghjklmnopqrstwxyz".split('');
            const zh ="啊把差大额发噶哈级卡啦吗那哦爬器然啥他哇西呀咋".split('');

            let segs = [];
            let curr;

            for(let i = 0, len = letters.length; i < len; i++ ) {
                curr = { letter: letters[i], data:[] };

                for (var j = 0; j < arr.length; j++ ) {
                    if( (arr[j].name && arr[j].name.split('')[0] == letters[i]) || (( !zh[i-1] || zh[i-1].localeCompare(arr[j].name, 'zh-Hans-CN', { sensitivity: 'accent' }) <= 0)
                                && arr[j].name.localeCompare(zh[i], 'zh-Hans-CN', { sensitivity: 'accent' }) == -1)) {
                        curr.data.push(arr[j]);
                    }
                }

                if(empty || curr.data.length) {
                    segs.push(curr);
                    curr.data.sort( (a,b) => {
                        return a.name.localeCompare(b.name);
                    });
                }
            }

            return segs;
      },
      /*通讯录*/
      getContactAjax(){
        homeContact().then(data=>{
          if(data.status){
            this.fuzzySearchData=data.data;
          }
          else{
            this.fuzzySearchData=[];
          }
          this.fuzzySearchArr=this.fuzzySearchData;
        });
      },
      /*快速通道*/
      getChannel(){
        HomeChannel().then(data=>{
          if(data.status){
            let sites = [],
              pages = Math.ceil(data.data.length/4);

            for(let i = 0;i<pages;i++){
              sites[i] = data.data.splice(0,4);
            }
            this.expresswayData=sites;
            this.expresswayPage=pages;
          }
          else{
            this.expresswayData=[];
            this.expresswayPage=1;
          }
        });
      },
      /*通知公告*/
      getNoticeAjax(){
        HomeNotice().then(data=>{
          if(data.status){
            this.NoticeData=data.data;
          }
          else{
            this.NoticeData=[];
          }
          this.$emit('on-notice-change',this.NoticeData.length);
        })
      }
    },
    filters:{
      getResultState(val){
        return val==='1'?'同意':
          val==='2'?'审批过期':
            val==='-1'?'不同意':
              val==='0'?'未审批':
                val==='5'?'正在审批':
                  val==='4'?'转发':'无'
      }
    },
    mounted(){
      /*fullCalendar插件*/
      const date=new Date();
      let _self=this;
      /*左边日历*/
      $('#homeCalendar1').fullCalendar({
        locale:'en',
        timeFormat: 'H:mm',
        axisFormat: 'H:mm',
        header:{
          left:'prev,next',
          center:'title',
          right: 'month,agendaWeek,agendaDay'
        },
        /*MMMM的显示文字*/
        monthNames:['1','2','3','4','5','6','7','8','9','10','11','12'],
        /*dddd的显示文字*/
        dayNames:['日','一','二','三','四','五','六'],
        /*      titleFormat:'YYYY年MMMM月',//日历头部文本显示信息
         columnFormat:'dddd M/D'||'M月d日 dddd'||'M月d日 dddd',//设置列【表头】显示文本*/
        views:{
          /*设置月、周、日title和column各自的显示文本*/
          month:{titleFormat:'YYYY年MMMM月',columnFormat:'D'},
          week:{titleFormat:'YYYY年MMMM月D日',columnFormat:'dddd M/D'},
          day:{titleFormat:'YYYY年MMMM月D日',columnFormat:'星期dddd'}
        },
        allDayText:'全天',//设置日中allDay的文本
        buttonText:{
          today:'今天',
          month:'月',
          week:'周',
          day:'日'
        },
        aspectRatio:1.97/*2.27*/,//宽高比
        weekMode:'liquid',//月视图的显示模式，fixed：固定显示6周高；liquid：高度随周数变化；variable: 高度固定
        dayClick:function(data){
          /*点击日历中的天【一个单元格】触发的事件*/
        },
        eventClick:function(event,jsevent,view){
          /*点击日程触发的事件*/
          /*跳转到event.start时间上*/
          const eventFormat=$.fullCalendar.moment(event.start).format();
          let eventDate=null;
          if(eventFormat.search('T')>0){
            eventDate=new Date(eventFormat.replace(/T/,' '));
          }else{
            eventDate=new Date(eventFormat);
          }
          $('#homeCalendar1 .fc-day-grid-event').css('background','#fe9963');
          $(jsevent.currentTarget).css('background','#4DA1FF');
          $('#homeCalendar2').fullCalendar('gotoDate',eventDate);
        },
        eventMouseover:function(event){
          /*移入日程触发的事件*/
        },
        eventMouseout:function(event){
          /*移除日程触发的事件*/
        },
        editable: true,
        eventLimit: true, // allow "more" link when too many events
        events:[],
        eventBorderColor:'transparent',
        eventBackgroundColor:'#fe9963',
        eventTextColor:'#fff'
      });
      $('#homeCalendar2').fullCalendar({
        locale:'en',
        timeFormat: 'H:mm',
        axisFormat: 'H:mm',
        customButtons: {            //自定义header属性中按钮[customButtons与header并用]
          myCustomButton: {
            text: '新建日程',
            click: _self.showNewSchedule/*显示新建日程弹框*/
          }
        },
        header:{
          left:'prev,next',
          right:'today,myCustomButton'
        },
        /*MMMM的显示文字*/
        monthNames:['1','2','3','4','5','6','7','8','9','10','11','12'],
        columnFormat:'YYYY/MMMM/DD 日程安排',//设置列【表头】显示文本
        buttonText:{
          today:'今天'
        },
        aspectRatio:0.58/*2.27*/,//宽高比
        weekMode:'liquid',//月视图的显示模式，fixed：固定显示6周高；liquid：高度随周数变化；variable: 高度固定
        defaultView:'basicDay',//控制日历content部分内容，会影响today点击效果
        eventClick:function(event){
          _self.updateEvent=event;
          _self.showHandlerSchedule(event);
        },/*点击日程触发的事件*/
        isLoading:true,
        editable: true,
        eventLimit: true, // allow "more" link when too many events
        /*events可为array，可为function，在请求返回数据为json的arr，也可为json*/
        events:[],
      });

      this.scheduleLoadAjax();
    },
    created(){
      this.BacklogList();
      /*通讯录*/
      this.getContactAjax();
      /*快速通道*/
      this.getChannel();
      /*通知公告*/
      this.getNoticeAjax();
    }
  }
</script>
<style lang="less" scoped>
  @import '../style/Home';
  @import '../style/Home.css';
  @import '../style/style';
  .alertDetail .el-dialog__body {
    padding: 1rem 20px;
  }
  .g-home{
    .Appset-right{
      height:27rem;
      margin-top: 2rem;
      border-radius: .4rem;
      overflow-y: auto;
      margin-bottom: 1rem;
      text-align: left;
    }
    .textarea{
      width: 100%;
      height:21.5rem;
      resize : none;
      border-radius: .4rem;
      padding: 1rem 0 0 1rem;
    }
    .submit{
      width: 8rem;
      padding-left: 1rem;
      padding-right: 1rem;
      border-radius: 1.1rem;
    }
    .Appset-right{
      border: 1px solid #d2d2d2;
    }
    .Appset-right-title{
      padding: .8rem 0 .8rem .8rem;
      font-weight: bold;
      font-size: 0.95rem;
      text-align: left;
    }
    .Appset-right-input1 .el-input__inner{
      height:28/16rem;
      border-radius:.8rem ;
    }
    .Appset-right-border{
      border: 1px solid #d2d2d2;
    }
    .el-tree{
      border: none;
      margin-bottom:3rem;
    }
    .LeaveRecord-dialog-title{
      display: inline-block;
      margin: auto;
      font-weight: bold;
      font-size: 16px;
      padding-bottom: 1.2rem;
    }
    .LeaveRecord-table-div{
      width: 100%;
      border-top: 1px solid #d2d2d2;
      text-align: center;
      line-height: 2.625rem;
      box-sizing:border-box;
    }
    .LeaveRecord-table-div-final{
      border-bottom: 1px solid #d2d2d2;
    }
    .LeaveRecord-state-btn{
      width: 6.25rem;
      height: 1.875rem;
      background:#4ba8ff;
      color:#fff;
      text-align: center;
      line-height: 1.875rem;
      border-top-right-radius: 1.1rem;
      border-bottom-right-radius: 1.1rem;
      margin-top: 1.2rem;
    }
    .LeaveRecord-agreed{
      width: 5rem;
      height: 2rem;
      text-align: center;
      display: inline-block;
      line-height: 2rem;
      cursor: pointer;
      font-size: 14px;
      color: #888888;
      border: 1px solid #d2d2d2;
      border-top-left-radius: 1rem;
      border-bottom-left-radius: 1rem;
    }
    .LeaveRecord-agreed:nth-of-type(3){
      width: 5rem;
      height: 2rem;
      text-align: center;
      display: inline-block;
      line-height: 2rem;
      cursor: pointer;
      font-size: 14px;
      margin-left: 5rem;
      color: #888888;
      border: 1px solid #d2d2d2;
      border-radius: 1rem;
    }
    .LeaveRecord-agreed:nth-of-type(2){
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
      border-top-right-radius: 1rem;
      border-bottom-right-radius: 1rem;
    }
    .active{
      background: #09baa7;
      color: #fff !important;
      border: 1px solid #09baa7;
    }
    .agreed2{
      border-top-right-radius: 1rem;
      border-bottom-right-radius: 1rem;
    }
    .Infor-input-inner .el-input__inner{
      border-radius: 1.2rem;
    }
    .ApprovelActive{
      color: #4da1ff;
    }
    .otherApprove{
      cursor: pointer;
    }
    .otherApprove:first-child{
      border-right: 1px solid #d2d2d2;
      padding-right:.6rem ;
    }
    .otherApprove:last-child{
      padding-left:.6rem ;
    }
  }
  #ThingPart{
    padding-left:1rem;
    .m-ThingLPMsg{
      display:inline-block;
      width: 14rem;
      white-space: nowrap;
      text-overflow:ellipsis;
      overflow:hidden;
    }
  }
  h3.alertDetail_title {
    text-align: center;
    margin-bottom: 1rem;
    font-size: 1rem;
  }

  .alertDetail_subTitle {
    font-size: 12px;
    padding: .8rem 0;
    border-top: 1px solid #dcdcdc;
    border-bottom: 1px solid #dcdcdc;
  }

  .alertDetail_subTitle_part {
    text-align: center;
  }

  .alertDetail_subTitle_time {
    text-align: right;
  }

  .alertDetail_center {
    min-height: 10rem;
    padding: 1.875rem 0;
  }

  .alertDetail_annex {
    border-radius: 0 18px 18px 0;
    -webkit-box-shadow: 0 5px 5px 1px #d2d2d2;
    -moz-box-shadow: 0 5px 5px 1px #d2d2d2;
    box-shadow: 0 5px 5px 1px #d2d2d2;
  }

  .alertDetail_list {
    margin-top: 1.875rem;
  }

  .alertDetail_list > div {
    float: left;
    border: 1px solid #fff;
    margin-right: 1.25rem;
    padding: 5px 10px;
  }

  .alertDetail_list > div:hover {
    border: 1px solid #d2d2d2;
    /*cursor: pointer;*/
  }

  .alertDetail_list > div > span {
    display: inline-block;
    max-width: 100px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-left: 8px;
  }

  .alertDetail .el-dialog__footer {
    -webkit-box-shadow: 0 -10px 20px -5px #d2d2d2;
    -moz-box-shadow: 0 -10px 20px -5px #d2d2d2;
    box-shadow: 0 -10px 20px -5px #d2d2d2;
    margin-top: 1rem;
    padding: 1rem 0;
  }

</style>






